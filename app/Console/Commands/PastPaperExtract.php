<?php

namespace App\Console\Commands;

use App\Services\PastPapers\PastPaperPdfExtractor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PastPaperExtract extends Command
{
    protected $signature = 'past-papers:extract {file : Relative path on the local disk} {--write : Save the unapproved JSON draft}';
    protected $description = 'Extract one source PDF into a reviewable past-paper draft';

    public function handle(PastPaperPdfExtractor $extractor): int
    {
        $relative = (string) $this->argument('file');
        $disk = Storage::disk('local');
        if (! $disk->exists($relative)) { $this->error('Source file not found on the local disk.'); return self::FAILURE; }
        $result = $extractor->extract($disk->path($relative), basename($relative));
        if ($result === null) { $this->error('Extraction unavailable or failed. Check LLM configuration and try again.'); return self::FAILURE; }
        $json = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        if ((bool) $this->option('write')) {
            $name = pathinfo($relative, PATHINFO_FILENAME).'-draft-'.substr(sha1($relative), 0, 8).'.json';
            $path = 'past-paper-source/extractions/'.$name;
            $disk->put($path, $json);
            $this->info("Draft saved: {$path}");
        } else { $this->line($json); }
        $this->line('Questions: '.count($result['draft']['questions'] ?? []).' (all remain unapproved).');
        return self::SUCCESS;
    }
}
