<?php

use App\Models\PastPaper;
use App\Models\PastPaperQuestion;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\PastPapers\PastPaperService;
use App\Services\PastPapers\PastPaperBankService;
use App\Services\PastPapers\PastPaperVariantService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function paperFamily(): array
{
    $guardian = User::factory()->create(['role' => 'guardian']);
    $student = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id]);
    $module = SyllabusModule::factory()->create(['subject' => 'Math']);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $module->id, 'status' => 'mastered']);
    $paper = PastPaper::create(['title' => 'SEA Math Starter', 'subject' => 'Math', 'provenance' => 'real', 'is_published' => true]);
    $question = PastPaperQuestion::create([
        'past_paper_id' => $paper->id, 'syllabus_module_id' => $module->id, 'number' => 1,
        'item_type' => 'mcq', 'prompt' => 'What is 2 + 2?', 'options' => ['3', '4', '5', '6'],
        'correct_answer' => '4', 'mark_scheme' => ['answer' => '4'], 'marks' => 1,
        'objective' => 'addition', 'difficulty' => 1, 'provenance' => 'real', 'qc_status' => 'approved',
    ]);
    return compact('guardian', 'student', 'paper', 'question');
}

it('composes a paper only from covered approved questions', function () {
    $family = paperFamily();

    $sitting = app(PastPaperService::class)->compose($family['student'], 'Math');

    expect($sitting->paper_code)->toStartWith('SEA-')
        ->and($sitting->question_ids)->toBe([$family['question']->id])
        ->and($sitting->status)->toBe('issued');
})->group('scenario:PP-09');

it('renders a printable paper without exposing the answer key', function () {
    $family = paperFamily();
    $sitting = app(PastPaperService::class)->compose($family['student'], 'Math');

    $response = $this->actingAs($family['guardian'])->get(route('guardian.past-papers.download', [$family['student'], $sitting]));

    $response->assertOk()->assertHeader('content-type', 'application/pdf');
})->group('scenario:PP-13');

it('stores uploaded pages and deterministically grades guardian-confirmed answers', function () {
    Storage::fake('local');
    $family = paperFamily();
    $sitting = app(PastPaperService::class)->compose($family['student'], 'Math');

    $this->actingAs($family['guardian'])
        ->post(route('guardian.past-papers.upload', [$family['student'], $sitting]), ['pages' => [UploadedFile::fake()->create('paper.pdf', 20, 'application/pdf')]])
        ->assertRedirect(route('guardian.past-papers.review', [$family['student'], $sitting]));

    $this->actingAs($family['guardian'])
        ->post(route('guardian.past-papers.grade', [$family['student'], $sitting]), ['answers' => [$family['question']->id => '4']])
        ->assertRedirect(route('guardian.past-papers.show', [$family['student'], $sitting]));

    $sitting->refresh();
    expect($sitting->status)->toBe('graded')->and($sitting->score)->toBe(1);
    expect(Storage::disk('local')->files("past-papers/{$family['student']->id}/{$sitting->id}"))->toHaveCount(1);
})->group('scenario:PP-16');

it('does not allow another guardian to view a child paper', function () {
    $family = paperFamily();
    $other = User::factory()->create(['role' => 'guardian']);
    $sitting = app(PastPaperService::class)->compose($family['student'], 'Math');

    $this->actingAs($other)->get(route('guardian.past-papers.show', [$family['student'], $sitting]))->assertForbidden();
})->group('scenario:PP-15');

it('requires a clearer rewrite when image OCR cannot read the page', function () {
    Storage::fake('local');
    $family = paperFamily();
    $sitting = app(PastPaperService::class)->compose($family['student'], 'Math');

    $this->actingAs($family['guardian'])
        ->post(route('guardian.past-papers.upload', [$family['student'], $sitting]), ['pages' => [UploadedFile::fake()->create('handwriting.jpg', 20, 'image/jpeg')]])
        ->assertRedirect(route('guardian.past-papers.review', [$family['student'], $sitting]));

    expect($sitting->submissions()->latest()->value('digitisation_status'))->toBe('rewrite_required');

    $this->actingAs($family['guardian'])
        ->post(route('guardian.past-papers.grade', [$family['student'], $sitting]), ['answers' => [$family['question']->id => '4']])
        ->assertRedirect(route('guardian.past-papers.review', [$family['student'], $sitting]));
})->group('scenario:PP-18');

it('keeps generated variants out of the live bank until QC approval', function () {
    $family = paperFamily();
    $variants = app(PastPaperVariantService::class)->generate($family['question'], 2);

    expect($variants)->toHaveCount(2)
        ->and($variants[0]->provenance)->toBe('generated')
        ->and($variants[0]->qc_status)->toBe('unapproved')
        ->and($variants[0]->correct_answer)->toBe('6');
})->group('scenario:PP-04');

it('exports and backs up the paper bank as a portable JSON snapshot', function () {
    Storage::fake('local');
    paperFamily();
    $service = app(PastPaperBankService::class);
    $payload = $service->export();
    $path = $service->backup();

    expect($payload['version'])->toBe(1)->and($payload['papers'])->toHaveCount(1);
    Storage::disk('local')->assertExists($path);
})->group('scenario:PP-01');
