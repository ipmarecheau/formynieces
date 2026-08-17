<?php

use App\Livewire\SchoolJournal;
use App\Livewire\StudentSchoolJournal;
use App\Models\SchoolJournalEntry;
use App\Models\SchoolStrandSignal;
use App\Models\User;
use App\Services\Motivation\DailyPlanComposer;
use App\Services\SchoolJournal\OcrService;
use App\Services\SchoolJournal\SchoolEvidenceService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/**
 * SJ-01..09 — the school journal. The OCR seam is faked throughout: the tests
 * prove the journal flow, not the vision model (that is benchmarked on real
 * papers in the browser).
 */
function guardianWithStudent(): array
{
    $guardian = User::factory()->create(['role' => 'guardian']);
    $student = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id]);

    return [$guardian, $student];
}

function fakeOcr(array $fields, array $confidence = [], string $text = 'transcribed text'): void
{
    $result = [
        'fields' => $fields,
        'text' => $text,
        'confidence' => $confidence ?: array_fill_keys(array_keys($fields), 0.95),
        'review' => [],
    ];
    foreach ($fields as $k => $v) {
        if (($confidence[$k] ?? 1) < 0.70) {
            $result['review'][] = $k;
        }
    }

    app()->instance(OcrService::class, new class($result) extends OcrService
    {
        public function __construct(private ?array $result) {}

        public function digitize(string $absolutePath, string $mime): ?array
        {
            return $this->result;
        }
    });
}

beforeEach(function () {
    Storage::fake('local');
});

/** SJ-01 — a student or guardian files a paper into the same journal. */
it('stores an uploaded paper from the guardian, dated and attached', function () {
    [$guardian, $student] = guardianWithStudent();
    fakeOcr(['subject' => 'ELA', 'score' => '18/20']);

    Livewire::actingAs($guardian)
        ->test(SchoolJournal::class, ['student' => $student])
        ->set('paper', UploadedFile::fake()->create('paper.jpg', 100, 'image/jpeg'))
        ->call('savePaper')
        ->assertHasNoErrors();

    $entry = SchoolJournalEntry::where('student_id', $student->id)->first();
    expect($entry)->not->toBeNull()
        ->and($entry->uploaded_by)->toBe('guardian')
        ->and($entry->assessment_date->isToday())->toBeTrue()
        ->and($entry->image_path)->toStartWith("school-journal/{$student->id}/");
    Storage::disk('local')->assertExists($entry->image_path);
})->group('scenario:SJ-01');

it('stores an uploaded paper from the student in the same journal, score-free', function () {
    $student = User::factory()->create(['role' => 'student']);
    fakeOcr(['score' => '2/20']); // even a weak mark — her screen never shows it

    Livewire::actingAs($student)
        ->test(StudentSchoolJournal::class)
        ->set('paper', UploadedFile::fake()->create('paper.jpg', 100, 'image/jpeg'))
        ->call('savePaper')
        ->assertHasNoErrors()
        ->assertSee('Paper filed')
        ->assertDontSee('2/20');

    expect(SchoolJournalEntry::where('student_id', $student->id)->where('uploaded_by', 'student')->count())->toBe(1);
})->group('scenario:SJ-01');

/** SJ-07 — the pipeline fills the fields and flags its low-confidence reads. */
it('digitises the assessment and flags low-confidence fields for human confirmation', function () {
    [$guardian, $student] = guardianWithStudent();
    fakeOcr(
        ['subject' => 'Mathematics', 'strand' => 'Number', 'assessment_type' => 'test', 'score' => '15/25', 'teacher_comment' => 'Review fractions'],
        ['subject' => 0.95, 'strand' => 0.4, 'assessment_type' => 0.9, 'score' => 0.45, 'teacher_comment' => 0.85],
        'full transcription',
    );

    Livewire::actingAs($guardian)
        ->test(SchoolJournal::class, ['student' => $student])
        ->set('paper', UploadedFile::fake()->create('paper.jpg', 100, 'image/jpeg'))
        ->call('savePaper');

    $entry = SchoolJournalEntry::where('student_id', $student->id)->first();
    expect($entry->digitisation_status)->toBe(SchoolJournalEntry::STATUS_DIGITISED)
        ->and($entry->subject)->toBe('Mathematics')
        ->and($entry->score)->toBe('15/25')
        ->and($entry->ocr_text)->toBe('full transcription')
        ->and($entry->ocr_confidence['strand'])->toBe(0.4);
})->group('scenario:SJ-07');

it('keeps the entry pending for manual entry when the pipeline cannot run', function () {
    [$guardian, $student] = guardianWithStudent();
    app()->instance(OcrService::class, new class extends OcrService
    {
        public function digitize(string $absolutePath, string $mime): ?array
        {
            return null;
        }
    });

    Livewire::actingAs($guardian)
        ->test(SchoolJournal::class, ['student' => $student])
        ->set('paper', UploadedFile::fake()->create('paper.jpg', 100, 'image/jpeg'))
        ->call('savePaper')
        ->assertSee('enter the details by hand');

    expect(SchoolJournalEntry::first()->digitisation_status)->toBe(SchoolJournalEntry::STATUS_PENDING);
})->group('scenario:SJ-07');

/** SJ-02 — the guardian is the final say; confirmation locks the evidence in. */
it('lets the guardian correct fields and confirm, recording strand signals', function () {
    [$guardian, $student] = guardianWithStudent();
    fakeOcr(['strand' => 'Grammar', 'score' => '18/20', 'subject' => 'ELA']);

    Livewire::actingAs($guardian)
        ->test(SchoolJournal::class, ['student' => $student])
        ->set('paper', UploadedFile::fake()->create('paper.jpg', 100, 'image/jpeg'))
        ->call('savePaper')
        ->set('form.score', '18/25')          // correct what the pipeline misread
        ->set('form.term', 'Term I 2026')
        ->call('confirmEntry')
        ->assertHasNoErrors();

    $entry = SchoolJournalEntry::first();
    expect($entry->score)->toBe('18/25')
        ->and($entry->digitisation_status)->toBe(SchoolJournalEntry::STATUS_CONFIRMED);
})->group('scenario:SJ-02');

/** SJ-08 — strong school work corroborates; it never masters anything on its own. */
it('records a corroborating signal for a strong strand but never marks a module mastered', function () {
    $student = User::factory()->create(['role' => 'student']);

    $entry = SchoolJournalEntry::create([
        'student_id' => $student->id,
        'uploaded_by' => 'guardian',
        'image_path' => 'school-journal/fake/paper.jpg',
        'strand' => 'Grammar',
        'score' => '19/20',
        'digitisation_status' => SchoolJournalEntry::STATUS_CONFIRMED,
    ]);

    app(SchoolEvidenceService::class)->recordSignals($entry);

    expect(app(SchoolEvidenceService::class)->corroborates($student->id, 'Grammar'))->toBeTrue()
        ->and(SchoolStrandSignal::count())->toBe(1)
        ->and(DB::table('student_progress')->where('student_id', $student->id)->count())->toBe(0); // no mastery written
})->group('scenario:SJ-08');

/** SJ-05 — a weak strand steers the daily plan, gently. */
it('surfaces a weak school strand as the day plan focus hint — kind and mark-free', function () {
    $student = User::factory()->create(['role' => 'student']);

    $entry = SchoolJournalEntry::create([
        'student_id' => $student->id,
        'uploaded_by' => 'guardian',
        'image_path' => 'school-journal/fake/paper.jpg',
        'strand' => 'Number',
        'score' => '8/25',
        'digitisation_status' => SchoolJournalEntry::STATUS_CONFIRMED,
    ]);
    app(SchoolEvidenceService::class)->recordSignals($entry);

    $plan = app(DailyPlanComposer::class)->forDay($student->id);

    expect($plan->focus_hint)->toContain('Number')
        ->and($plan->focus_hint)->toContain('suggestion')
        ->and($plan->focus_hint)->not->toContain('8/25');   // no marks in the child's world
})->group('scenario:SJ-05');

/** SJ-06 — a school mark never touches the child's motivational world. */
it('a weak school result never breaks a streak or blocks anything', function () {
    $student = User::factory()->create(['role' => 'student']);

    $entry = SchoolJournalEntry::create([
        'student_id' => $student->id,
        'uploaded_by' => 'guardian',
        'image_path' => 'school-journal/fake/paper.jpg',
        'strand' => 'Writing',
        'score' => '5/25',
        'digitisation_status' => SchoolJournalEntry::STATUS_CONFIRMED,
    ]);
    app(SchoolEvidenceService::class)->recordSignals($entry);

    // Streak state untouched by school evidence — her motivational tables never hear about it.
    expect(DB::table('student_progress')->where('student_id', $student->id)->count())->toBe(0)
        ->and(DB::table('school_strand_signals')->where('student_id', $student->id)->count())->toBe(1);

    // …and the weak signal steers nothing harsh: no duty is added or forced.
    $plan = app(DailyPlanComposer::class)->forDay($student->id);
    expect(array_keys($plan->duties))->not->toContain('school_remedy')
        ->and($plan->duties)->not->toContain(true); // nothing force-completed or gated
})->group('scenario:SJ-06');

/** SJ-03/SJ-09 — timeline by term, trend across terms. */
it('groups the journal by term newest-first and trends strands across terms', function () {
    [$guardian, $student] = guardianWithStudent();

    foreach ([
        ['term' => 'Term I 2026', 'date' => '2026-01-20', 'strand' => 'Grammar', 'score' => '12/20'],
        ['term' => 'Term I 2026', 'date' => '2026-02-10', 'strand' => 'Number', 'score' => '17/20'],
        ['term' => 'Term II 2026', 'date' => '2026-05-05', 'strand' => 'Grammar', 'score' => '18/20'],
    ] as $e) {
        SchoolJournalEntry::create([
            'student_id' => $student->id,
            'uploaded_by' => 'guardian',
            'image_path' => 'school-journal/fake/paper.jpg',
            'assessment_date' => $e['date'],
            'term' => $e['term'],
            'strand' => $e['strand'],
            'score' => $e['score'],
            'digitisation_status' => SchoolJournalEntry::STATUS_CONFIRMED,
        ]);
    }

    $component = Livewire::actingAs($guardian)
        ->test(SchoolJournal::class, ['student' => $student]);

    $grouped = $component->instance()->groupedEntries();
    expect($grouped->keys()->all())->toBe(['Term II 2026', 'Term I 2026'])   // newest term first
        ->and($grouped['Term II 2026']->first()->strand)->toBe('Grammar');   // newest entry first

    $trend = app(SchoolEvidenceService::class)->trendByTerm($student->id);
    expect(count($trend))->toBe(2)
        ->and($trend[0]['term'])->toBe('Term II 2026');
})->group('scenario:SJ-03', 'scenario:SJ-09');

/** SJ-04 — the weekly summary includes labelled school evidence. */
it('includes this week school evidence, labelled, in the guardian dashboard', function () {
    [$guardian, $student] = guardianWithStudent();

    SchoolJournalEntry::create([
        'student_id' => $student->id,
        'uploaded_by' => 'guardian',
        'image_path' => 'school-journal/fake/paper.jpg',
        'assessment_date' => now()->toDateString(),
        'term' => 'Term I 2026',
        'strand' => 'Grammar',
        'assessment_type' => 'test',
        'score' => '16/20',
        'digitisation_status' => SchoolJournalEntry::STATUS_CONFIRMED,
    ]);

    $this->actingAs($guardian)
        ->get(route('guardian.dashboard'))
        ->assertOk()
        ->assertSee('From school')
        ->assertSee('16/20')
        ->assertSee('never merged into');   // the two sources are labelled apart
})->group('scenario:SJ-04');

// SJ-10 — a guardian opens her student's journal from the dashboard without a 500.
it('opens the guardian journal view without error, even with no entries', function () {
    [$guardian, $student] = guardianWithStudent();

    $this->actingAs($guardian)
        ->get(route('guardian.journal', $student))
        ->assertOk()
        ->assertSeeText('School Journal');   // the term timeline renders, no MissingLayoutException
})->group('scenario:SJ-10');
