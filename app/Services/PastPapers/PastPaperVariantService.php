<?php
namespace App\Services\PastPapers;
use App\Models\PastPaperQuestion;
class PastPaperVariantService
{
    public function generate(PastPaperQuestion $seed, int $count = 1): array
    {
        $made = [];
        if (! preg_match('/What is (\d+) \+ (\d+)\??/i', $seed->prompt, $m)) {
            $seed->update(['qc_status' => 'rejected', 'qc_reason' => 'No verified variant generator supports this question type yet.']);
            return [];
        }
        for ($i = 1; $i <= min($count, 10); $i++) {
            $a = (int) $m[1] + $i; $b = (int) $m[2] + $i; $answer = (string) ($a + $b);
            $made[] = PastPaperQuestion::create([
                'past_paper_id' => $seed->past_paper_id, 'syllabus_module_id' => $seed->syllabus_module_id, 'seed_question_id' => $seed->id,
                'number' => $seed->number + 1000 + $i, 'item_type' => $seed->item_type, 'prompt' => "What is {$a} + {$b}?",
                'correct_answer' => $answer, 'mark_scheme' => ['answer' => $answer], 'marks' => $seed->marks, 'objective' => $seed->objective,
                'difficulty' => $seed->difficulty, 'provenance' => 'generated', 'qc_status' => 'unapproved',
                'qc_reason' => 'Generated from a verified arithmetic template; requires admin approval.',
            ]);
        }
        return $made;
    }
}
