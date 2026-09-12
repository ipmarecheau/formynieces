<?php

namespace App\Console\Commands;

use App\Services\PastPapers\PastPaperTopicMapper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PastPaperMapTopics extends Command
{
    protected $signature = 'past-papers:map-topics {draft : Draft filename in past-paper-source/extractions}';
    protected $description = 'Map an extracted draft to SEA modules and difficulty metadata';

    public function handle(PastPaperTopicMapper $mapper): int
    {
        $name = basename((string) $this->argument('draft'));
        $disk = Storage::disk('local'); $path = 'past-paper-source/extractions/'.$name;
        if (! $disk->exists($path)) { $this->error('Draft not found.'); return self::FAILURE; }
        $draft = $mapper->map(json_decode($disk->get($path), true) ?: []);
        $disk->put($path, json_encode($draft, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $this->info('SEA metadata mapped and draft saved: '.$name);
        return self::SUCCESS;
    }
}
