<?php

declare(strict_types=1);

namespace App\Services\QuestionBank;

use DOMDocument;
use DOMElement;

/**
 * The single entry point the admin import UI calls. A Moodle export may contain
 * multichoice practice questions, essay writing prompts, or both; this coordinator
 * partitions the file by question type and hands each slice to the importer that
 * owns it — multichoice to the practice bank, essay to the writing bank — then
 * merges the two outcomes into one report.
 *
 * Partitioning (rather than running each importer on the whole file) keeps the
 * reports clean: neither importer ever reports the other's questions as skipped.
 */
class QuestionImportCoordinator
{
    public function __construct(
        private MoodleQuestionImporter $practiceImporter,
        private WritingPromptImporter $writingImporter,
    ) {}

    public function import(string $xml, bool $dryRun = true): ImportReport
    {
        $doc = new DOMDocument;
        if (! @$doc->loadXML($xml, LIBXML_NOCDATA | LIBXML_PARSEHUGE)) {
            $report = new ImportReport;
            $report->dryRun = $dryRun;
            $report->skip('(file)', 'The file is not valid XML.');

            return $report;
        }

        $merged = new ImportReport;
        $merged->dryRun = $dryRun;

        if ($this->hasType($doc, 'multichoice')) {
            $this->mergeInto($merged, $this->practiceImporter->import($this->sliceTo($xml, 'multichoice'), $dryRun));
        }

        if ($this->hasType($doc, 'essay')) {
            $this->mergeInto($merged, $this->writingImporter->import($this->sliceTo($xml, 'essay'), $dryRun));
        }

        return $merged;
    }

    private function hasType(DOMDocument $doc, string $type): bool
    {
        foreach ($doc->getElementsByTagName('question') as $q) {
            /** @var DOMElement $q */
            if ($q->getAttribute('type') === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the XML with only `category` questions and questions of $keepType —
     * every other question node removed — so the target importer sees only its own
     * type (categories are shared context and kept for both).
     */
    private function sliceTo(string $xml, string $keepType): string
    {
        $doc = new DOMDocument;
        @$doc->loadXML($xml, LIBXML_NOCDATA | LIBXML_PARSEHUGE);

        $remove = [];
        foreach ($doc->getElementsByTagName('question') as $q) {
            /** @var DOMElement $q */
            $type = $q->getAttribute('type');
            if ($type !== 'category' && $type !== $keepType) {
                $remove[] = $q;
            }
        }
        foreach ($remove as $node) {
            $node->parentNode?->removeChild($node);
        }

        return (string) $doc->saveXML();
    }

    private function mergeInto(ImportReport $target, ImportReport $source): void
    {
        $target->parsed += $source->parsed;
        $target->created += $source->created;
        $target->updated += $source->updated;
        $target->imagesStored += $source->imagesStored;

        foreach ($source->skipped as $skip) {
            $target->skipped[] = $skip;
        }
        foreach ($source->byModule as $moduleId => $count) {
            $target->byModule[$moduleId] = ($target->byModule[$moduleId] ?? 0) + $count;
        }
    }
}
