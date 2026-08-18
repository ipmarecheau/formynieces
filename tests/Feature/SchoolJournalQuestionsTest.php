<?php

use App\Livewire\SchoolJournal;
use App\Livewire\StudentSchoolJournal;
use App\Models\SchoolJournalEntry;
use App\Models\SchoolJournalQuestion;
use App\Models\SchoolStrandSignal;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Motivation\DailyPlanComposer;
use App\Services\SchoolJournal\JournalDigitiser;
use App\Services\SchoolJournal\OcrService;
use App\Services\SchoolJournal\SchoolEvidenceService;
use App\Services\SchoolJournal\TopicMatcher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/**
 * SJ-11..13 — the per-question breakdown: syllabus-aligned topics, clipped
 * question images, and the AI's read of the child's answer + reasoning
 * (honest layer only). The vision seam is faked; the matcher runs for real.
 */
beforeEach(function () {
    Storage::fake('local');

    $this->guardian = User::factory()->create(['role' => 'guardian']);
    $this->student = User::factory()->create(['role' => 'student', 'parent_id' => $this->guardian->id]);

    $this->module = SyllabusModule::create([
        'code' => 'ELA-001',
        'subject' => 'ELA',
        'topic' => 'Plurals: consonant + y',
        'sea_section' => 'ELA',
        'sequence_order' => 1,
    ]);

    $this->module2 = SyllabusModule::create([
        'code' => 'ELA-014',
        'subject' => 'ELA',
        'topic' => 'Subject-verb agreement',
        'sea_section' => 'ELA',
        'sequence_order' => 14,
    ]);

    $this->questions = [
        [
            'number' => 1,
            'prompt' => 'Write the plural of city',
            'student_answer' => 'citys',
            'correct_answer' => 'cities',
            'is_correct' => false,
            'topic' => 'Plurals consonant y',
            'module_code' => 'ELA-001',
            'topic_confidence' => 0.95,
            'reasoning_note' => 'Wrote citys — applied the plain add-s rule, not the y→ies rule.',
            'box' => [40, 180, 420, 400],
        ],
        [
            'number' => 2,
            'prompt' => 'Use stories in a sentence',
            'student_answer' => 'My grandpa tells me funny stories.',
            'correct_answer' => null,
            'is_correct' => true,
            'topic' => 'Subject-verb agreement',
            'module_code' => 'ELA-014',
            'topic_confidence' => 0.9,
            'reasoning_note' => null,
            'box' => [40, 420, 700, 560],
        ],
        [
            'number' => 3,
            'prompt' => 'Pick the correct verb',
            'student_answer' => 'walk',
            'correct_answer' => 'walks',
            'is_correct' => false,
            'topic' => 'something completely unrelated maybe perhaps',
            'module_code' => null,
            'topic_confidence' => 0.2,
            'reasoning_note' => 'Chose the base verb — subject-verb agreement with third person singular.',
            'box' => 'junk',
        ],
    ];

    app()->instance(OcrService::class, new class($this->questions) extends OcrService
    {
        public function __construct(private array $questions) {}

        public function digitize(string $absolutePath, string $mime): ?array
        {
            return [
                'fields' => ['subject' => 'ELA', 'strand' => 'Grammar', 'score' => '18/20'],
                'text' => 'transcription',
                'confidence' => ['subject' => 0.95, 'strand' => 0.9, 'assessment_type' => 0.9, 'score' => 0.9, 'teacher_comment' => 0.9],
                'review' => [],
                'questions' => $this->questions,
            ];
        }
    });
});

/** SJ-11 — each question stored with its syllabus alignment; weak matches flagged, never guessed. */
it('stores the per-question breakdown aligned to syllabus topics', function () {
    $entry = SchoolJournalEntry::create([
        'student_id' => $this->student->id,
        'uploaded_by' => 'guardian',
        'image_path' => 'school-journal/fake/paper.jpg',
        'assessment_date' => now()->toDateString(),
    ]);

    Storage::disk('local')->put('school-journal/fake/paper.jpg', 'bytes');
    app(JournalDigitiser::class)->digitise($entry, 'image/jpeg');

    $questions = $entry->questions()->orderBy('number')->get();
    expect($questions)->toHaveCount(3)
        ->and($questions[0]->syllabus_module_id)->toBe($this->module->id)       // module_code match
        ->and($questions[1]->syllabus_module_id)->toBe($this->module2->id)      // second topic aligned
        ->and($questions[2]->syllabus_module_id)->toBeNull()                   // unmatched by design → flagged, not guessed
        ->and($questions[0]->student_answer)->toBe('citys')
        ->and($questions[0]->correct_answer)->toBe('cities')
        ->and($questions[0]->is_correct)->toBeFalse()
        ->and($questions[2]->syllabus_module_id)->toBeNull()                   // unmatched → flagged, not guessed
        ->and($questions[2]->topic_label)->toBe('something completely unrelated maybe perhaps');
})->group('scenario:SJ-11');

it('aligns via fuzzy topic labels when no module code is given', function () {
    $match = app(TopicMatcher::class)->match('plurals consonant y → ies', null, 'ELA');

    expect($match['module']?->id)->toBe($this->module->id)
        ->and($match['confidence'])->toBeGreaterThanOrEqual(0.5);
})->group('scenario:SJ-11');

/** SJ-12 — valid boxes are stored for clipping; junk boxes fall back to the full page. */
it('stores a clip box per question and degrades to the full page on junk boxes', function () {
    $entry = SchoolJournalEntry::create([
        'student_id' => $this->student->id,
        'uploaded_by' => 'guardian',
        'image_path' => 'school-journal/fake/paper.jpg',
        'assessment_date' => now()->toDateString(),
    ]);
    Storage::disk('local')->put('school-journal/fake/paper.jpg', 'bytes');

    app(JournalDigitiser::class)->digitise($entry, 'image/jpeg');

    $questions = $entry->questions()->orderBy('number')->get();
    expect($questions[0]->clip_box)->toBe([40, 180, 420, 400])                 // question + its marking
        ->and($questions[2]->clip_box)->toBeNull();                            // junk box → full-page fallback
})->group('scenario:SJ-12');

it('serves the clip image to the owning guardian only', function () {
    $entry = SchoolJournalEntry::create([
        'student_id' => $this->student->id,
        'uploaded_by' => 'guardian',
        'image_path' => 'school-journal/fake/paper.jpg',
        'assessment_date' => now()->toDateString(),
    ]);
    Storage::disk('local')->put('school-journal/fake/paper.jpg', 'bytes');
    app(JournalDigitiser::class)->digitise($entry, 'image/jpeg');

    $question = $entry->questions()->orderBy('number')->first();

    $this->actingAs($this->guardian)
        ->get(route('guardian.journal.clip', $question))
        ->assertOk();

    $stranger = User::factory()->create(['role' => 'guardian']);
    $this->actingAs($stranger)
        ->get(route('guardian.journal.clip', $question))
        ->assertForbidden();

    $this->actingAs($this->student)
        ->get(route('guardian.journal.clip', $question))
        ->assertForbidden();                                                   // honest layer only
})->group('scenario:SJ-12');

/** SJ-13 — reasoning notes exist for the guardian, never the child; wrong answers steer precisely. */
it('shows the reasoning note to the guardian beside the clip', function () {
    $entry = SchoolJournalEntry::create([
        'student_id' => $this->student->id,
        'uploaded_by' => 'guardian',
        'image_path' => 'school-journal/fake/paper.jpg',
        'assessment_date' => now()->toDateString(),
    ]);
    Storage::disk('local')->put('school-journal/fake/paper.jpg', 'bytes');
    app(JournalDigitiser::class)->digitise($entry, 'image/jpeg');

    Livewire::actingAs($this->guardian)
        ->test(SchoolJournal::class, ['student' => $this->student])
        ->assertSee('y→ies rule')                                              // the reasoning note
        ->assertSee('ELA-001 — '.$this->module->topic);                        // the syllabus alignment
})->group('scenario:SJ-13');

it('never shows questions, answers, or reasoning in the student world', function () {
    Livewire::actingAs($this->student)
        ->test(StudentSchoolJournal::class)
        ->set('paper', UploadedFile::fake()->create('paper.jpg', 100, 'image/jpeg'))
        ->call('savePaper')
        ->assertHasNoErrors()
        ->assertSee('Paper filed')
        ->assertDontSee('citys')
        ->assertDontSee('y→ies rule')
        ->assertDontSee('18/20');

    expect(SchoolJournalQuestion::whereHas('entry', fn ($q) => $q->where('student_id', $this->student->id))->count())->toBe(3); // stored for the honest layer, shown never
})->group('scenario:SJ-13');

it('steers the daily plan by syllabus topic once questions are confirmed', function () {
    $entry = SchoolJournalEntry::create([
        'student_id' => $this->student->id,
        'uploaded_by' => 'guardian',
        'image_path' => 'school-journal/fake/paper.jpg',
        'assessment_date' => now()->toDateString(),
    ]);
    Storage::disk('local')->put('school-journal/fake/paper.jpg', 'bytes');
    app(JournalDigitiser::class)->digitise($entry, 'image/jpeg');
    $entry->update(['digitisation_status' => SchoolJournalEntry::STATUS_CONFIRMED]);

    app(SchoolEvidenceService::class)->recordSignals($entry);

    // A per-module signal exists for each topic on the paper…
    expect(SchoolStrandSignal::where('syllabus_module_id', $this->module2->id)
        ->where('direction', 'corroborates')->exists())->toBeTrue();   // q2 right → corroborates
    expect(SchoolStrandSignal::where('syllabus_module_id', $this->module->id)
        ->where('direction', 'weakens')->exists())->toBeTrue();        // q1 wrong → weakens

    // …and the day's focus prefers the syllabus topic over the bare strand.
    $focus = app(SchoolEvidenceService::class)->weakestFocus($this->student->id);
    expect($focus)->not->toBeNull()
        ->and($focus[0])->toBe($this->module->topic)
        ->and($focus[1])->toBeTrue();

    $plan = app(DailyPlanComposer::class)->forDay($this->student->id);
    expect($plan->focus_hint)->toContain($this->module->topic)
        ->and($plan->focus_hint)->not->toContain('18/20');
})->group('scenario:SJ-13');
