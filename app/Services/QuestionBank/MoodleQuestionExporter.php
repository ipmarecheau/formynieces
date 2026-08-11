<?php

declare(strict_types=1);

namespace App\Services\QuestionBank;

use App\Models\PracticeQuestion;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Facades\Storage;

/**
 * Exports the practice bank back to Moodle XML — the same dialect the importer
 * reads, so a round-trip is lossless.
 *
 * Questions are grouped under one category per syllabus module, coded "M<id>" so
 * a re-import addresses the module directly. The practice rung is written back as
 * a difficulty level (1→D1, 3→D3, 5→D5) that the importer maps back to the same
 * rung. Embedded figures are re-encoded as base64 <file> elements with
 *
 * @@PLUGINFILE@@ references, exactly as Moodle exports them.
 */
class MoodleQuestionExporter
{
    /** climb rung => Moodle D-level. The bank now stores the real levels (1/3/5),
     *  so this is the identity — kept explicit for round-trip clarity. */
    private array $rungToLevel = [1 => 1, 3 => 3, 5 => 5];

    /**
     * @param  iterable<PracticeQuestion>|null  $questions  defaults to the whole active bank
     */
    public function export(?iterable $questions = null): string
    {
        $questions ??= PracticeQuestion::with('module')
            ->orderBy('module_id')->orderBy('difficulty')->orderBy('id')->get();

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;
        $quiz = $doc->appendChild($doc->createElement('quiz'));

        $currentModule = null;
        foreach ($questions as $q) {
            if ($q->module_id !== $currentModule) {
                $currentModule = $q->module_id;
                $topic = $q->module?->topic ?? ('Module '.$q->module_id);
                $quiz->appendChild($this->categoryNode($doc, (int) $q->module_id, $topic));
            }
            $quiz->appendChild($this->questionNode($doc, $q));
        }

        return (string) $doc->saveXML();
    }

    private function categoryNode(DOMDocument $doc, int $moduleId, string $topic): DOMElement
    {
        $question = $doc->createElement('question');
        $question->setAttribute('type', 'category');
        $category = $question->appendChild($doc->createElement('category'));
        $category->appendChild($this->cdataChild($doc, 'text', "\$course\$/top/SEA Bank/M{$moduleId} {$topic}"));

        return $question;
    }

    private function questionNode(DOMDocument $doc, PracticeQuestion $q): DOMElement
    {
        $topic = $q->module?->topic ?? ('Module '.$q->module_id);
        $level = $this->rungToLevel[$q->difficulty] ?? $q->difficulty;

        $question = $doc->createElement('question');
        $question->setAttribute('type', 'multichoice');

        $name = $question->appendChild($doc->createElement('name'));
        $name->appendChild($this->cdataChild($doc, 'text', "M{$q->module_id} · D{$level} · #{$q->id} — {$topic}"));

        // questiontext — prompt HTML with any figures re-embedded as <file>.
        $questiontext = $doc->createElement('questiontext');
        $questiontext->setAttribute('format', 'html');
        [$html, $files] = $this->reembedFigures($doc, (string) $q->prompt);
        $questiontext->appendChild($this->cdataChild($doc, 'text', $html));
        foreach ($files as $file) {
            $questiontext->appendChild($file);
        }
        $question->appendChild($questiontext);

        $generalfeedback = $doc->createElement('generalfeedback');
        $generalfeedback->setAttribute('format', 'html');
        $generalfeedback->appendChild($this->cdataChild($doc, 'text', (string) $q->explanation));
        $question->appendChild($generalfeedback);

        $question->appendChild($this->textElement($doc, 'single', 'true'));
        $question->appendChild($this->textElement($doc, 'answernumbering', 'abc'));

        foreach (array_values($q->options ?? []) as $i => $optionText) {
            $answer = $doc->createElement('answer');
            $answer->setAttribute('fraction', $i === $q->correct_index ? '100' : '0');
            $answer->setAttribute('format', 'html');
            $answer->appendChild($this->cdataChild($doc, 'text', (string) $optionText));
            $question->appendChild($answer);
        }

        return $question;
    }

    /**
     * Rewrite stored figure URLs (/storage/question-media/…) back to Moodle's
     *
     * @@PLUGINFILE@@ references and return the base64 <file> nodes to embed.
     *
     * @return array{0: string, 1: list<DOMElement>}
     */
    private function reembedFigures(DOMDocument $doc, string $html): array
    {
        $files = [];

        if (! preg_match_all('#/storage/(question-media/[^"\'\s>]+)#', $html, $matches)) {
            return [$html, $files];
        }

        foreach (array_unique($matches[1]) as $path) {
            if (! Storage::disk('public')->exists($path)) {
                continue;
            }
            $name = basename($path);
            $data = base64_encode(Storage::disk('public')->get($path));

            $file = $doc->createElement('file', $data);
            $file->setAttribute('name', $name);
            $file->setAttribute('path', '/');
            $file->setAttribute('encoding', 'base64');
            $files[] = $file;

            $html = str_replace('/storage/'.$path, '@@PLUGINFILE@@/'.$name, $html);
        }

        return [$html, $files];
    }

    private function textElement(DOMDocument $doc, string $tag, string $value): DOMElement
    {
        $el = $doc->createElement($tag);
        $el->appendChild($doc->createTextNode($value));

        return $el;
    }

    /** A <tag><![CDATA[value]]></tag>-style element (value wrapped in a <text> CDATA node when needed). */
    private function cdataChild(DOMDocument $doc, string $tag, string $value): DOMElement
    {
        $el = $doc->createElement($tag);
        $el->appendChild($doc->createCDATASection($value));

        return $el;
    }
}
