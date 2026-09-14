<?php

namespace App\Console\Commands;

use App\Services\PastPapers\PastPaperPageVisionExtractor;
use App\Services\PastPapers\PastPaperTopicMapper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PastPaperRebuildFromPages extends Command
{
    protected $signature = 'past-papers:rebuild-from-pages {draft : Draft filename in past-paper-source/extractions} {--page= : Rebuild one source page only}';
    protected $description = 'Rebuild a draft page-by-page from rendered paper images, including faithful SVG diagrams';

    public function handle(PastPaperPageVisionExtractor $extractor, PastPaperTopicMapper $mapper): int
    {
        $name = basename((string) $this->argument('draft'));
        $path = 'past-paper-source/extractions/'.$name;
        $disk = Storage::disk('local');
        if (! $disk->exists($path)) {
            $this->error('Draft not found.');
            return self::FAILURE;
        }

        $result = $extractor->rebuild(
            json_decode($disk->get($path), true) ?: [],
            $this->option('page') !== null ? (int) $this->option('page') : null,
        );
        $result['draft'] = $mapper->map($result['draft']);
        $disk->put($path, json_encode($result['draft'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $this->info("Rebuilt {$result['questions']} questions with {$result['diagrams']} SVG diagram(s); {$result['skipped']} page(s) could not be read.");

        return self::SUCCESS;
    }
}
