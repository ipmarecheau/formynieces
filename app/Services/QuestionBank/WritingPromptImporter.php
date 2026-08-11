<?php

declare(strict_types=1);

namespace App\Services\QuestionBank;

use App\Models\WritingBankPrompt;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Str;

/**
 * Imports SEA writing prompts from a Moodle XML export (essay-type questions) into
 * the `writing_bank_prompts` bank.
 *
 * Authoring convention this reads:
 *   - Only `essay` questions are imported; anything else is reported and skipped.
 *   - The question name carries genre, difficulty and a version tag as
 *     "M<n> · D<1-5> · v<NN> — <Genre>: <Sub-genre>". Genre and sub-genre are read
 *     from the topic label after the em-dash (the leading M-code is ignored — it is
 *     a stale artefact); genre selects the syllabus module via GENRE_MODULE_MAP.
 *   - Difficulty D1-D5 collapses onto the three rungs via config difficulty_map.
 *   - The marking rubric travels in <graderinfo> as an HTML table plus a machine
 *     block "<!-- RUBRIC_JSON: {…} -->"; the JSON is parsed and stored, the HTML kept.
 *
 * Idempotent: each prompt is keyed by its Moodle name in `source_ref`, so
 * re-importing the same file updates rather than duplicates.
 */
class WritingPromptImporter
{
    /** @var array<string, int> genre => syllabus_modules.id */
    private const GENRE_MODULE_MAP = [
        WritingBankPrompt::GENRE_NARRATIVE => 69,
        WritingBankPrompt::GENRE_REPORT => 70,
    ];

    /**
     * D-level => writing rung. Writing keeps its own 3-band scale (1 easy, 2 medium,
     * 3 hard) — it is NOT the mastery climb, so it does not use the practice bank's
     * D1/D3/D5 remap.
     *
     * @var array<int, int>
     */
    private const DIFFICULTY_MAP = [1 => 1, 2 => 1, 3 => 2, 4 => 3, 5 => 3];

    public function import(string $xml, bool $dryRun = true): ImportReport
    {
        $report = new ImportReport;
        $report->dryRun = $dryRun;

        $doc = new DOMDocument;
        // Moodle exports are trusted admin content; suppress libxml warnings and
        // capture parse failures as a single reported error.
        $ok = @$doc->loadXML($xml, LIBXML_NOCDATA | LIBXML_PARSEHUGE);
        if (! $ok) {
            $report->skip('(file)', 'The file is not valid XML.');

            return $report;
        }

        foreach ($doc->getElementsByTagName('question') as $q) {
            /** @var DOMElement $q */
            $type = $q->getAttribute('type');

            if ($type === 'category') {
                continue;
            }

            $name = trim($this->textValue($this->namedNode($q, 'name')));

            if ($type !== 'essay') {
                $report->skip($name !== '' ? $name : '(unnamed)', "Unsupported question type '{$type}' — only essay prompts import to the writing bank.");

                continue;
            }

            $this->importPrompt($q, $name, $dryRun, $report);
        }

        return $report;
    }

    private function importPrompt(DOMElement $q, string $name, bool $dryRun, ImportReport $report): void
    {
        $report->parsed++;

        [$genre, $subGenre] = $this->genreFromName($name);
        if ($genre === null) {
            $report->skip($name, 'No recognised genre (Narrative/Report) in the question name.');

            return;
        }

        $rung = $this->difficultyRungFromName($name);
        if ($rung === null) {
            $report->skip($name, 'No difficulty (a D1-D5 level) found in the question name.');

            return;
        }

        $moduleId = self::GENRE_MODULE_MAP[$genre] ?? null;
        $graderInfo = $this->textValue($this->namedNode($q, 'graderinfo'));

        $attributes = [
            'genre' => $genre,
            'sub_genre' => $subGenre,
            'module_id' => $moduleId,
            'difficulty' => $rung,
            'title' => $subGenre,
            'prompt' => $this->sanitizeHtml($this->textValue($this->questionTextNode($q))),
            'rubric' => $this->parseRubricJson($graderInfo),
            'rubric_html' => $this->sanitizeHtml($this->stripRubricJson($graderInfo)),
            'marks' => (int) ($this->textValue($this->namedNode($q, 'defaultgrade')) ?: 20),
            'is_active' => true,
        ];

        $exists = WritingBankPrompt::where('source_ref', $name)->exists();
        $exists ? $report->updated++ : $report->created++;
        if ($moduleId !== null) {
            $report->byModule[$moduleId] = ($report->byModule[$moduleId] ?? 0) + 1;
        }

        if (! $dryRun) {
            WritingBankPrompt::updateOrCreate(['source_ref' => $name], $attributes);
        }
    }

    /**
     * Genre + sub-genre from the topic label after the em-dash, e.g.
     * "M90 · D1 · v01 — Narrative: Story Including a Given Line" =>
     * ['narrative', 'Story Including a Given Line'].
     *
     * @return array{0: ?string, 1: string}
     */
    private function genreFromName(string $name): array
    {
        $topic = trim((string) Str::afterLast($name, '—'));
        $prefix = strtolower(trim((string) Str::before($topic, ':')));
        $subGenre = trim((string) Str::after($topic, ':'));

        $genre = match ($prefix) {
            'narrative' => WritingBankPrompt::GENRE_NARRATIVE,
            'report' => WritingBankPrompt::GENRE_REPORT,
            default => null,
        };

        return [$genre, $subGenre];
    }

    private function difficultyRungFromName(string $name): ?int
    {
        if (preg_match('/\b(?:level|d)\s*([1-5])\b/i', $name, $m)) {
            return self::DIFFICULTY_MAP[(int) $m[1]] ?? null;
        }

        return null;
    }

    /**
     * Extract and decode the "<!-- RUBRIC_JSON: {…} -->" block from grader info.
     *
     * @return array<string, mixed>|null
     */
    private function parseRubricJson(string $graderInfo): ?array
    {
        if (! preg_match('/RUBRIC_JSON:\s*(\{.*\})\s*-->/s', $graderInfo, $m)) {
            return null;
        }

        $decoded = json_decode(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);

        return is_array($decoded) ? $decoded : null;
    }

    private function stripRubricJson(string $graderInfo): string
    {
        return (string) preg_replace('/<!--\s*RUBRIC_JSON:.*?-->/s', '', $graderInfo);
    }

    /**
     * Neutralise anything executable while keeping formatting. The source is
     * trusted admin content, so this is defence-in-depth rather than hardening.
     */
    private function sanitizeHtml(string $html): string
    {
        $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $html = preg_replace('#\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html) ?? $html;
        $html = preg_replace('#(href|src)\s*=\s*(["\']?)\s*javascript:[^"\'>\s]*\2#i', '$1=$2#$2', $html) ?? $html;

        return trim($html);
    }

    private function questionTextNode(DOMElement $q): ?DOMElement
    {
        return $this->namedNode($q, 'questiontext');
    }

    /** First child element with the given tag name. */
    private function namedNode(DOMElement $q, string $tag): ?DOMElement
    {
        foreach ($q->getElementsByTagName($tag) as $n) {
            /** @var DOMElement $n */
            return $n;
        }

        return null;
    }

    /**
     * The value of a Moodle field element — each wraps its value in a direct
     * <text> child. Reads that child so nested markup is never mistaken for value.
     */
    private function textValue(?DOMElement $el): string
    {
        if ($el === null) {
            return '';
        }

        foreach ($el->childNodes as $child) {
            if ($child instanceof DOMElement && $child->tagName === 'text') {
                return $child->textContent;
            }
        }

        return '';
    }
}
