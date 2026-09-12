<?php

namespace App\Http\Controllers;

use App\Models\PaperSitting;
use App\Models\PastPaperQuestion;
use App\Models\User;
use App\Services\PastPapers\PastPaperService;
use App\Services\PastPapers\PaperDigitiser;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PastPaperController extends Controller
{
    public function index(Request $request, User $student): View
    {
        $this->guardStudent($request, $student);

        return view('guardian.past-papers', [
            'student' => $student,
            'sittings' => PaperSitting::where('student_id', $student->id)->with('paper')->latest('issued_at')->get(),
            'subjects' => ['Math', 'ELA'],
        ]);
    }

    public function store(Request $request, User $student, PastPaperService $service): RedirectResponse
    {
        $this->guardStudent($request, $student);
        $data = $request->validate(['subject' => ['required', 'in:Math,ELA'], 'length' => ['required', 'in:short,standard,long']]);
        $sitting = $service->compose($student, $data['subject'], $data['length']);

        return redirect()->route('guardian.past-papers.show', [$student, $sitting])->with('paper_created', true);
    }

    public function show(Request $request, User $student, PaperSitting $sitting): View
    {
        $this->guardStudent($request, $student);
        abort_unless($sitting->student_id === $student->id, 404);
        $sitting->load(['paper', 'answers.question']);
        $questions = PastPaperQuestion::whereIn('id', $sitting->question_ids)->orderBy('number')->get();

        $graded = PaperSitting::where('student_id', $student->id)->where('status', 'graded')->where('id', '<=', $sitting->id)->latest('graded_at')->get();
        $trend = $graded->map(fn ($item) => ['date' => optional($item->graded_at)->format('j M'), 'score' => $item->total_marks ? round($item->score / $item->total_marks * 100) : 0])->values();
        $byTopic = $sitting->answers->load('question.module')->groupBy(fn ($answer) => $answer->question->module?->topic ?? 'General')->map(fn ($answers, $topic) => ['topic' => $topic, 'correct' => $answers->where('is_correct', true)->count(), 'total' => $answers->count()])->values();
        return view('guardian.past-paper-show', compact('student', 'sitting', 'questions', 'trend', 'byTopic'));
    }

    public function download(Request $request, User $student, PaperSitting $sitting)
    {
        $this->guardStudent($request, $student);
        abort_unless($sitting->student_id === $student->id, 404);
        $sitting->load('paper');
        $questions = PastPaperQuestion::whereIn('id', $sitting->question_ids)->orderBy('number')->get();

        return Pdf::loadView('pdf.past-paper', compact('student', 'sitting', 'questions'))
            ->setPaper('a4')->download($sitting->paper_code.'.pdf');
    }

    public function upload(Request $request, User $student, PaperSitting $sitting, PaperDigitiser $digitiser): RedirectResponse
    {
        $this->guardStudent($request, $student);
        abort_unless($sitting->student_id === $student->id, 404);
        $request->validate(['pages' => ['required', 'array', 'min:1'], 'pages.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240']]);
        $paths = collect($request->file('pages'))->map(fn ($file) => $file->store("past-papers/{$student->id}/{$sitting->id}", 'local'))->all();
        $submission = $sitting->submissions()->create(['image_paths' => $paths, 'digitisation_status' => 'pending']);
        $sitting->load('paper');
        $digitiser->digitise($sitting);
        $sitting->update(['status' => 'uploaded']);

        return redirect()->route('guardian.past-papers.review', [$student, $sitting])->with('upload_saved', true);
    }

    public function review(Request $request, User $student, PaperSitting $sitting): View
    {
        $this->guardStudent($request, $student);
        abort_unless($sitting->student_id === $student->id, 404);
        $sitting->load(['paper', 'submissions', 'answers.question']);
        $questions = PastPaperQuestion::whereIn('id', $sitting->question_ids)->orderBy('number')->get();

        return view('guardian.past-paper-review', compact('student', 'sitting', 'questions'));
    }

    public function grade(Request $request, User $student, PaperSitting $sitting, PastPaperService $service): RedirectResponse
    {
        $this->guardStudent($request, $student);
        abort_unless($sitting->student_id === $student->id, 404);
        $questions = PastPaperQuestion::whereIn('id', $sitting->question_ids)->get();
        $answers = $request->validate(collect($questions)->mapWithKeys(fn ($q) => ["answers.{$q->id}" => ['nullable', 'string', 'max:500']])->all());
        $service->grade($sitting, $answers['answers'] ?? []);

        return redirect()->route('guardian.past-papers.show', [$student, $sitting])->with('graded', true);
    }

    private function guardStudent(Request $request, User $student): void
    {
        abort_unless($student->isStudent() && $student->parent_id === $request->user()->id, 403);
    }
}
