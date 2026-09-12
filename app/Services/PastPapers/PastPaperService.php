<?php

namespace App\Services\PastPapers;

use App\Models\PaperAnswer;
use App\Models\PaperSitting;
use App\Models\PastPaper;
use App\Models\PastPaperQuestion;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PastPaperService
{
    /** Compose only approved questions attached to modules the child has reached. */
    public function compose(User $student, string $subject, string $length = 'short'): PaperSitting
    {
        $covered = $student->progress()
            ->whereIn('status', ['mastered', 'inferred_mastered', 'needs_work'])
            ->pluck('module_id');

        $paper = PastPaper::query()
            ->where('subject', $subject)
            ->where('is_published', true)
            ->with(['publishedQuestions' => fn ($q) => $q->whereIn('syllabus_module_id', $covered)->with('module')])
            ->firstOrFail();

        $limit = ['short' => 4, 'standard' => 10, 'long' => 20][$length] ?? 4;
        $used = PaperSitting::where('student_id', $student->id)->pluck('question_ids')->flatten()->map(fn ($id) => (int) $id);
        $questions = $paper->publishedQuestions->reject(fn ($q) => $used->contains($q->id))->take($limit);
        if ($questions->isEmpty()) {
            $questions = $paper->publishedQuestions->take($limit);
        }
        abort_if($questions->isEmpty(), 422, 'There are no approved questions for topics this child has covered yet.');

        return PaperSitting::create([
            'student_id' => $student->id,
            'past_paper_id' => $paper->id,
            'paper_code' => 'SEA-'.strtoupper(Str::random(8)),
            'status' => 'issued',
            'subject' => $subject,
            'length' => $length,
            'question_ids' => $questions->pluck('id')->values()->all(),
            'issued_at' => now()->toDateString(),
            'total_marks' => $questions->sum('marks'),
        ]);
    }

    /** Deterministic first-pass grading for answers confirmed by the guardian. */
    public function grade(PaperSitting $sitting, array $answers): void
    {
        $questions = PastPaperQuestion::whereIn('id', $sitting->question_ids)->get()->keyBy('id');
        DB::transaction(function () use ($sitting, $answers, $questions): void {
            $score = 0;
            foreach ($questions as $question) {
                $given = trim((string) ($answers[$question->id] ?? ''));
                $correct = mb_strtolower($given) === mb_strtolower(trim((string) $question->correct_answer));
                $marks = $correct ? $question->marks : 0;
                PaperAnswer::updateOrCreate(
                    ['paper_sitting_id' => $sitting->id, 'past_paper_question_id' => $question->id],
                    ['read_answer' => $given, 'is_correct' => $correct, 'marks_awarded' => $marks, 'confidence' => 1.0],
                );
                $score += $marks;
            }
            $sitting->update(['status' => 'graded', 'score' => $score, 'graded_at' => now()]);
        });
    }
}
