<?php

namespace App\Services\PastPapers;

use App\Models\SyllabusModule;
use App\Services\LlmService;

class PastPaperTopicMapper
{
    public function __construct(private LlmService $llm) {}

    public function map(array $draft): array
    {
        $modules = SyllabusModule::orderBy('subject')->orderBy('sequence_order')->get(['code','subject','topic','sea_section','description']);
        $questions = collect($draft['draft']['questions'] ?? [])->map(fn ($q) => ['number' => $q['number'] ?? null, 'prompt' => $q['prompt'] ?? ''])->values()->all();
        $result = $this->llm->completeJson(
            'You are an SEA curriculum editor. Map each question to exactly one module from the supplied list. Do not invent modules. Return JSON only in the shape {"questions":[{"number":1,"module_code":"MATH-001","topic":"...","objective":"...","difficulty":1}]}. Difficulty is 1 to 5 and should match the question demand, not wording length.',
            "MODULES:\n".$modules->map(fn ($m) => "{$m->code} | {$m->subject} | {$m->sea_section} | {$m->topic} | {$m->description}")->implode("\n")."\n\nQUESTIONS:\n".json_encode($questions, JSON_UNESCAPED_SLASHES),
            5000,
        );
        $valid = $modules->keyBy('code');
        foreach ((array) ($result['questions'] ?? []) as $mapped) {
            $number = (int) ($mapped['number'] ?? 0);
            $index = collect($draft['draft']['questions'] ?? [])->search(fn ($q) => (int) ($q['number'] ?? 0) === $number);
            if ($index === false || ! $valid->has($mapped['module_code'] ?? '')) continue;
            $module = $valid->get($mapped['module_code']);
            $draft['draft']['questions'][$index]['module_code'] = $module->code;
            $draft['draft']['questions'][$index]['topic'] = (string) ($mapped['topic'] ?: $module->topic);
            $draft['draft']['questions'][$index]['objective'] = (string) ($mapped['objective'] ?? $module->topic);
            $draft['draft']['questions'][$index]['difficulty'] = max(1, min(5, (int) ($mapped['difficulty'] ?? 3)));
        }
        $draft['draft']['paper']['year'] ??= $this->yearFromFilename($draft['filename'] ?? '');
        return $draft;
    }

    private function yearFromFilename(string $filename): ?int
    {
        return preg_match('/(20\d{2})/', $filename, $match) ? (int) $match[1] : null;
    }
}
