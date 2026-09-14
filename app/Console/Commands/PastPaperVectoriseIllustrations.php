<?php

namespace App\Console\Commands;

use App\Services\PastPapers\PastPaperIllustrationVectorizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PastPaperVectoriseIllustrations extends Command
{
    protected $signature = 'past-papers:vectorise-illustrations {draft : Draft filename in past-paper-source/extractions} {--page= : Generate only one source page} {--overwrite : Replace existing SVG drafts}';
    protected $description = 'Generate reviewable SVG diagrams from rendered source-paper pages';

    public function handle(PastPaperIllustrationVectorizer $vectorizer): int
    {
        $name = basename((string) $this->argument('draft'));
        $path = 'past-paper-source/extractions/'.$name;
        $disk = Storage::disk('local');
        if (! $disk->exists($path)) {
            $this->error('Draft not found.');
            return self::FAILURE;
        }

        $result = $vectorizer->vectorise(
            json_decode($disk->get($path), true) ?: [],
            $this->option('page') !== null ? (int) $this->option('page') : null,
            (bool) $this->option('overwrite'),
        );
        $disk->put($path, json_encode($result['draft'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $this->info("Generated {$result['generated']} SVG diagram(s); {$result['review']} question(s) need visual QC; {$result['skipped']} skipped (page image unavailable).");

        return self::SUCCESS;
    }
}
