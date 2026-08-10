<?php

use App\Models\PracticeQuestion;
use App\Models\QuestionBankBackup;
use App\Services\QuestionBank\QuestionBankBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
});

it('takes a safety backup before emptying the bank', function () {
    PracticeQuestion::factory()->count(5)->create();

    $backup = app(QuestionBankBackupService::class)->deleteAll();

    expect($backup->reason)->toBe('before-delete-all')
        ->and($backup->question_count)->toBe(5)
        ->and(Storage::disk('local')->exists($backup->path))->toBeTrue()
        ->and(PracticeQuestion::count())->toBe(0); // bank emptied
})->group('scenario:QB-11');

it('snapshots the whole bank on the daily run', function () {
    PracticeQuestion::factory()->count(7)->create();

    $backup = app(QuestionBankBackupService::class)->runDaily();

    expect($backup->reason)->toBe('daily')
        ->and($backup->question_count)->toBe(7);

    $captured = json_decode(Storage::disk('local')->get($backup->path), true);
    expect($captured)->toHaveCount(7);
})->group('scenario:QB-12');

it('prunes backups older than 30 days and keeps recent ones', function () {
    $service = app(QuestionBankBackupService::class);
    PracticeQuestion::factory()->create();

    $old = $service->backup('daily');
    $old->forceFill(['created_at' => now()->subDays(40)])->save();
    $oldPath = $old->path;

    $recent = $service->backup('daily');

    $service->runDaily(); // takes today's + prunes

    expect(QuestionBankBackup::whereKey($old->id)->exists())->toBeFalse()
        ->and(Storage::disk('local')->exists($oldPath))->toBeFalse()
        ->and(QuestionBankBackup::whereKey($recent->id)->exists())->toBeTrue();
})->group('scenario:QB-13');

it('restores the bank exactly to a chosen backup', function () {
    $service = app(QuestionBankBackupService::class);

    $keep = PracticeQuestion::factory()->create(['prompt' => 'Original A']);
    $alsoKeep = PracticeQuestion::factory()->create(['prompt' => 'Original B']);

    $backup = $service->backup('manual');

    // The bank changes after the backup.
    $keep->delete();
    PracticeQuestion::factory()->create(['prompt' => 'Added later']);

    $service->restore($backup);

    expect(PracticeQuestion::count())->toBe(2)
        ->and(PracticeQuestion::pluck('prompt')->sort()->values()->all())->toBe(['Original A', 'Original B'])
        ->and(PracticeQuestion::whereKey($keep->id)->exists())->toBeTrue(); // exact ids restored
})->group('scenario:QB-14');
