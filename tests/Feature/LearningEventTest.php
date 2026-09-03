<?php

use App\Models\ReteachSession;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Practice\Remediation;
use App\Support\LearningEvent;
use Illuminate\Support\Facades\Log;

it('records a scenario-tagged event to the learning channel', function () {
    Log::shouldReceive('channel')->once()->with('learning')->andReturnSelf();
    Log::shouldReceive('info')->once()->withArgs(function (string $event, array $ctx): bool {
        return $event === 'reteach.started'
            && $ctx['scenario'] === 'LL-14'
            && $ctx['student_id'] === 7
            && $ctx['props']['module_id'] === 3;
    });

    LearningEvent::record('LL-14', 'reteach.started', ['module_id' => 3], 7);
});

it('emits reteach.started tagged LL-14 when a streak-triggered re-teach begins', function () {
    $student = User::factory()->create();
    $module = SyllabusModule::factory()->create();

    Log::shouldReceive('channel')->with('learning')->andReturnSelf();
    Log::shouldReceive('info')->once()->withArgs(function (string $event, array $ctx): bool {
        return $event === 'reteach.started' && $ctx['scenario'] === 'LL-14';
    });

    app(Remediation::class)->start($student->id, $module->id, ReteachSession::TRIGGER_STREAK);
});

it('tags a 5-of-7 window trigger as LL-22', function () {
    $student = User::factory()->create();
    $module = SyllabusModule::factory()->create();

    Log::shouldReceive('channel')->with('learning')->andReturnSelf();
    Log::shouldReceive('info')->once()->withArgs(fn (string $e, array $c): bool => $c['scenario'] === 'LL-22');

    app(Remediation::class)->start($student->id, $module->id, ReteachSession::TRIGGER_WINDOW);
});

it('does not re-emit when an open re-teach already exists', function () {
    $student = User::factory()->create();
    $module = SyllabusModule::factory()->create();
    app(Remediation::class)->start($student->id, $module->id, ReteachSession::TRIGGER_STREAK);

    // A second start with an active session returns it WITHOUT recording a new event.
    Log::shouldReceive('channel')->with('learning')->andReturnSelf();
    Log::shouldReceive('info')->never();

    app(Remediation::class)->start($student->id, $module->id, ReteachSession::TRIGGER_STREAK);
});
