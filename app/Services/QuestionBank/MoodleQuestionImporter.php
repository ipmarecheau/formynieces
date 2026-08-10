<?php

declare(strict_types=1);

namespace App\Services\QuestionBank;

use App\Models\PracticeQuestion;
use App\Models\SyllabusModule;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Imports SEA practice questions from a standard Moodle XML export into the
 * `practice_questions` bank.
 *
 * Authoring convention this reads (see config/question_import.php):
 *   - Questions are grouped under Moodle categories "…/Qnn <label>" (e.g. "Q04
 *     Fraction shaded"); the leading Qnn code selects the syllabus module.
 *   - Each question name carries its difficulty as "… · D<1-5> · …"; D1-D5 map
 *     onto the three practice rungs.
 *   - Only single-answer 4-option `multichoice` questions are imported; anything
 *     else is reported and skipped rather than guessed at.
 *   - Figures embedded as base64 <file> elements are extracted to the public
 *     disk and their @@PLUGINFILE@@ references rewritten to real URLs.
 *
 * Idempotent: each question is keyed by its Moodle name in practice_questions
 * .source_ref, so re-importing the same file updates rather than duplicates.
 */
class MoodleQuestionImporter
{
    /** @var array<string, int> Qnn => syllabus_modules.id */
    private array $skillMap;

    /** @var array<int, int> D-level => rung */
    private array $difficultyMap;

    private string $mediaDir;

    /** @var array<int, SyllabusModule> id => module (cached) */
    private array $modules;

    public function __construct()
    {
        $this->skillMap = config('question_import.skill_module_map', []);
        $this->difficultyMap = config('question_import.difficulty_map', [1 => 1, 2 => 1, 3 => 2, 4 => 3, 5 => 3]);
        $this->mediaDir = config('question_import.media_directory', 'question-media');
        $this->modules = SyllabusModule::all()->keyBy('id')->all();
    }

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

        $currentSkill = null;

        foreach ($doc->getElementsByTagName('question') as $q) {
            /** @var DOMElement $q */
            $type = $q->getAttribute('type');

            if ($type === 'category') {
                $currentSkill = $this->skillCodeFromCategory($this->textValue($this->namedNode($q, 'category')));

                continue;
            }

            $name = trim($this->textValue($this->namedNode($q, 'name')));

            if ($type !== 'multichoice') {
                $report->skip($name !== '' ? $name : '(unnamed)', "Unsupported question type '{$type}'.");

                continue;
            }

            $this->importQuestion($q, $name, $currentSkill, $dryRun, $report);
        }

        return $report;
    }

    private function importQuestion(DOMElement $q, string $name, ?string $currentSkill, bool $dryRun, ImportReport $report): void
    {
        $report->parsed++;

        // The skill code leads the question name (e.g. B01, S01, Q01, M6). Fall
        // back to the current category if a name somehow omits it.
        $skill = $this->skillCodeFromName($name) ?? $currentSkill;
        $moduleId = $this->resolveModuleId($skill);

        if ($moduleId === null || ! isset($this->modules[$moduleId])) {
            $report->skip($name, 'No syllabus module is mapped to skill '.($skill ?? '(unknown)').'.');

            return;
        }

        $rung = $this->difficultyRungFromName($name);
        if ($rung === null) {
            $report->skip($name, 'No difficulty (a 3-band word or a level number) found in the question name.');

            return;
        }

        // Answers: exactly four, exactly one fully correct.
        $options = [];
        $correctIndex = null;
        foreach ($q->getElementsByTagName('answer') as $i => $answer) {
            /** @var DOMElement $answer */
            $options[] = $this->plainText($this->textValue($answer));
            if ((float) $answer->getAttribute('fraction') >= 100.0) {
                $correctIndex = $correctIndex === null ? $i : -1; // -1 flags "more than one correct"
            }
        }

        if (count($options) !== 4) {
            $report->skip($name, 'Expected 4 options, found '.count($options).'.');

            return;
        }
        if ($correctIndex === null || $correctIndex === -1) {
            $report->skip($name, 'A question must have exactly one fully-correct answer.');

            return;
        }

        // Prompt + explanation, with embedded figures extracted and rewritten.
        $prompt = $this->processHtml($this->questionTextNode($q), $dryRun, $report);
        $explanation = $this->processHtml($this->namedNode($q, 'generalfeedback'), $dryRun, $report);

        $module = $this->modules[$moduleId];

        $attributes = [
            'module_id' => $moduleId,
            'subject' => $module->subject,
            'sea_section' => $module->sea_section,
            'strand' => $module->strand,
            'difficulty' => $rung,
            'prompt' => $prompt,
            'options' => $options,
            'correct_index' => $correctIndex,
            'explanation' => $explanation !== '' ? $explanation : null,
            'is_active' => true,
        ];

        $exists = PracticeQuestion::where('source_ref', $name)->exists();
        $exists ? $report->updated++ : $report->created++;
        $report->byModule[$moduleId] = ($report->byModule[$moduleId] ?? 0) + 1;

        if (! $dryRun) {
            PracticeQuestion::updateOrCreate(['source_ref' => $name], $attributes);
        }
    }

    /**
     * Extract base64 <file> figures from a Moodle text node, store them on the
     * public disk (keyed by content hash so re-imports dedupe), rewrite the
     *
     * @@PLUGINFILE@@ references to real URLs, and sanitise the HTML.
     */
    private function processHtml(?DOMElement $node, bool $dryRun, ImportReport $report): string
    {
        if ($node === null) {
            return '';
        }

        $html = $this->textValue($node);
        if ($html === '') {
            return '';
        }

        foreach ($node->getElementsByTagName('file') as $file) {
            /** @var DOMElement $file */
            $fileName = $file->getAttribute('name');
            $data = base64_decode(trim($file->textContent), true);
            if ($fileName === '' || $data === false) {
                continue;
            }

            $ext = pathinfo($fileName, PATHINFO_EXTENSION) ?: 'png';
            $stored = $this->mediaDir.'/'.sha1($data).'.'.strtolower($ext);

            if (! $dryRun) {
                if (! Storage::disk('public')->exists($stored)) {
                    Storage::disk('public')->put($stored, $data);
                }
            }
            $report->imagesStored++;

            $url = '/storage/'.$stored;
            // Moodle references the file as @@PLUGINFILE@@/<name>, name possibly url-encoded.
            $html = str_replace(
                ['@@PLUGINFILE@@/'.$fileName, '@@PLUGINFILE@@/'.rawurlencode($fileName)],
                [$url, $url],
                $html
            );
        }

        return $this->sanitizeHtml($html);
    }

    /**
     * Neutralise anything executable while keeping formatting and figures. The
     * source is trusted admin content, so this is defence-in-depth rather than
     * hostile-input hardening.
     */
    private function sanitizeHtml(string $html): string
    {
        // Drop script/style/iframe/object/embed blocks entirely.
        $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', $html) ?? $html;
        // Strip inline event handlers (on*=) and javascript: URIs.
        $html = preg_replace('#\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html) ?? $html;
        $html = preg_replace('#(href|src)\s*=\s*(["\']?)\s*javascript:[^"\'>\s]*\2#i', '$1=$2#$2', $html) ?? $html;

        return trim($html);
    }

    /**
     * Resolve a category/skill code to a syllabus module id. A "Qnn" code is
     * looked up in the skill map; an "Mnn" code (used by this app's own exports)
     * addresses a module directly.
     */
    private function resolveModuleId(?string $code): ?int
    {
        if ($code === null) {
            return null;
        }

        if (preg_match('/^M(\d+)$/i', $code, $m)) {
            $id = (int) $m[1];

            return isset($this->modules[$id]) ? $id : null;
        }

        return $this->skillMap[strtoupper($code)] ?? null;
    }

    private function skillCodeFromCategory(string $category): ?string
    {
        $last = trim((string) Str::afterLast($category, '/'));

        return preg_match('/^([A-Za-z]+\d+)/', $last, $m) ? strtoupper($m[1]) : null;
    }

    /**
     * The skill code leads the question name — a letter prefix + number, covering
     * every bank dialect: Q01 (original), A01/B01 (combined sets), S/P/G/V/F/C
     * (ELA), and M6 (this app's own exports).
     */
    private function skillCodeFromName(string $name): ?string
    {
        return preg_match('/^([A-Za-z]+\d+)/', trim($name), $m) ? strtoupper($m[1]) : null;
    }

    /**
     * The practice rung (1 easy, 2 medium, 3 hard) from whichever difficulty
     * scheme the bank uses:
     *   - 3-band words: "Easier" / "Same (as SEA)" / "Harder"/"Much harder"
     *   - numeric levels: "Level 1–5" or "D1–5" (collapsed via difficulty_map)
     */
    private function difficultyRungFromName(string $name): ?int
    {
        // Numeric levels first (also how this app's own exports encode difficulty),
        // so a module topic in the name can never be mistaken for a band word.
        if (preg_match('/\b(?:level|d)\s*([1-5])\b/i', $name, $m)) {
            return $this->difficultyMap[(int) $m[1]] ?? null;
        }

        $lower = strtolower($name);

        if (str_contains($lower, 'easier')) {
            return 1;
        }
        if (str_contains($lower, 'harder')) { // covers "much harder"
            return 3;
        }
        if (str_contains($lower, 'same')) {
            return 2;
        }

        return null;
    }

    private function questionTextNode(DOMElement $q): ?DOMElement
    {
        return $this->namedNode($q, 'questiontext');
    }

    /** First direct-ish child element with the given tag name. */
    private function namedNode(DOMElement $q, string $tag): ?DOMElement
    {
        foreach ($q->getElementsByTagName($tag) as $n) {
            /** @var DOMElement $n */
            return $n;
        }

        return null;
    }

    /**
     * The value of a Moodle field element — every one (name, category,
     * questiontext, generalfeedback, answer) wraps its value in a direct <text>
     * child. Reads that child so nested <file> data is never mistaken for value.
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

    private function plainText(string $html): string
    {
        return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
