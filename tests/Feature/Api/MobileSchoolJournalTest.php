<?php

use App\Models\SchoolJournalEntry;
use App\Models\SchoolJournalQuestion;
use App\Models\User;
use App\Services\SchoolJournal\JournalDigitiser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/** @return array{0: User, 1: User, 2: string} */
function guardianWithChild(): array
{
    $parent = User::factory()->create(['role' => 'guardian']);
    $child = User::factory()->create(['role' => 'student', 'parent_id' => $parent->id]);

    return [$parent, $child, $parent->createToken('t', ['parent'])->plainTextToken];
}

it('returns the school-journal timeline grouped by term with per-question breakdown (SJ-03/04)', function () {
    [$parent, $child, $token] = guardianWithChild();

    $entry = SchoolJournalEntry::create([
        'student_id' => $child->id, 'uploaded_by' => 'guardian', 'image_path' => 'x.jpg',
        'assessment_date' => '2026-03-10', 'term' => 'Term 2', 'subject' => 'Math',
        'strand' => 'Number', 'assessment_type' => 'Test', 'score' => '18/20',
        'digitisation_status' => SchoolJournalEntry::STATUS_CONFIRMED,
    ]);
    SchoolJournalQuestion::create([
        'school_journal_entry_id' => $entry->id, 'number' => 1, 'prompt' => 'What is 2+2?',
        'student_answer' => '4', 'correct_answer' => '4', 'is_correct' => true, 'topic_label' => 'Addition',
    ]);

    $this->withToken($token)->getJson("/api/mobile/children/{$child->id}/journal")
        ->assertOk()
        ->assertJsonPath('terms.0.term', 'Term 2')
        ->assertJsonPath('terms.0.entries.0.score', '18/20')
        ->assertJsonPath('terms.0.entries.0.questions.0.topic_label', 'Addition')
        ->assertJsonStructure([
            'child' => ['id', 'name'],
            'terms' => [['term', 'entries' => [['id', 'date', 'subject', 'score', 'status', 'questions' => [['number', 'prompt', 'is_correct']]]]]],
            'trend',
        ]);
});

it('uploads a graded paper and files it (SJ-01)', function () {
    Storage::fake('local');
    [$parent, $child, $token] = guardianWithChild();

    // The OCR seam is exercised elsewhere; here it reports it could not read the paper.
    $this->mock(JournalDigitiser::class, fn ($m) => $m->shouldReceive('digitise')->andReturnFalse());

    $this->withToken($token)->postJson("/api/mobile/children/{$child->id}/journal", [
        'paper' => UploadedFile::fake()->create('paper.jpg', 100, 'image/jpeg'),
    ])
        ->assertCreated()
        ->assertJsonPath('digitised', false)
        ->assertJsonStructure(['digitised', 'note', 'entry' => ['id', 'status', 'questions']]);

    $this->assertDatabaseHas(SchoolJournalEntry::class, [
        'student_id' => $child->id, 'digitisation_status' => SchoolJournalEntry::STATUS_PENDING,
    ]);
});

it('rejects a non-paper upload', function () {
    Storage::fake('local');
    [$parent, $child, $token] = guardianWithChild();

    $this->withToken($token)->postJson("/api/mobile/children/{$child->id}/journal", [
        'paper' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
    ])->assertStatus(422)->assertJsonValidationErrors('paper');
});

it('confirms a journal entry’s corrected details (SJ-02)', function () {
    [$parent, $child, $token] = guardianWithChild();

    $entry = SchoolJournalEntry::create([
        'student_id' => $child->id, 'uploaded_by' => 'guardian', 'image_path' => 'x.jpg',
        'assessment_date' => '2026-03-10', 'digitisation_status' => SchoolJournalEntry::STATUS_PENDING,
    ]);

    $this->withToken($token)->postJson("/api/mobile/children/{$child->id}/journal/{$entry->id}/confirm", [
        'assessment_date' => '2026-03-12', 'term' => 'Term 2', 'subject' => 'Math', 'score' => '19/20',
    ])
        ->assertOk()
        ->assertJsonPath('entry.status', SchoolJournalEntry::STATUS_CONFIRMED)
        ->assertJsonPath('entry.score', '19/20');

    $this->assertDatabaseHas(SchoolJournalEntry::class, [
        'id' => $entry->id, 'digitisation_status' => SchoolJournalEntry::STATUS_CONFIRMED, 'subject' => 'Math',
    ]);
});

it('forbids reading another guardian’s child journal (403)', function () {
    [$parentA, $childA] = guardianWithChild();
    $tokenB = User::factory()->create(['role' => 'guardian'])->createToken('t', ['parent'])->plainTextToken;

    $this->withToken($tokenB)->getJson("/api/mobile/children/{$childA->id}/journal")->assertForbidden();
});
