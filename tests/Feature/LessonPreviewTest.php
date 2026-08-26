<?php

use App\Livewire\LessonWalk;
use App\Models\Lesson;
use App\Models\ModuleStageCompletion;
use App\Models\SyllabusModule;
use App\Models\User;
use Livewire\Livewire;

/**
 * LE-11 — an admin can walk any lesson in the real student renderer, as a student or in the
 * re-teach flow, with nothing recorded, so lessons can be verified on an ongoing basis.
 */
function lpModuleWithLesson(): SyllabusModule
{
    $module = SyllabusModule::create([
        'subject' => 'Math', 'topic' => 'Rounding', 'sea_section' => 'A',
        'sequence_order' => 1, 'pacing_week' => 1, 'description' => 'Round numbers.',
        'resources' => [], 'code' => 'MATH-PRV',
    ]);
    Lesson::create([
        'module_id' => $module->id, 'is_published' => true, 'title' => 'Rounding',
        'blocks' => [['type' => 'text', 'content' => 'Round to the nearest ten.']],
    ]);

    return $module;
}

function lpAdmin(): User
{
    return User::create(['name' => 'Admin', 'email' => 'admin-'.uniqid().'@t.com', 'password' => bcrypt('x'), 'role' => 'admin']);
}

it('lets an admin open the student preview of a lesson', function () {
    $this->actingAs(lpAdmin());
    $this->get(route('admin.lessons.preview', lpModuleWithLesson()->id))->assertOk();
});

it('walks the preview in student mode with the re-teach flow off', function () {
    Livewire::actingAs(lpAdmin())
        ->test(LessonWalk::class, ['module' => lpModuleWithLesson(), 'mode' => 'student'])
        ->assertSet('previewMode', 'student')
        ->assertSet('reteach', false)
        ->assertSet('guidedLocked', false);
});

it('walks the preview in re-teach mode with the re-teach flow on', function () {
    Livewire::actingAs(lpAdmin())
        ->test(LessonWalk::class, ['module' => lpModuleWithLesson(), 'mode' => 'reteach'])
        ->assertSet('previewMode', 'reteach')
        ->assertSet('reteach', true);
});

it('records nothing when an admin previews a lesson (no stage completion)', function () {
    $admin = lpAdmin();
    $module = lpModuleWithLesson();

    // A single-block lesson completes on mount; in preview it must NOT record the lesson stage.
    Livewire::actingAs($admin)
        ->test(LessonWalk::class, ['module' => $module, 'mode' => 'student'])
        ->assertSet('lessonComplete', true);

    expect(ModuleStageCompletion::where('student_id', $admin->id)->where('module_id', $module->id)->exists())->toBeFalse();
});

it('forbids a student from opening the admin lesson preview', function () {
    $student = User::create(['name' => 'S', 'email' => 's-'.uniqid().'@t.com', 'password' => bcrypt('x'), 'role' => 'student', 'onboarding_completed_at' => now()]);
    $this->actingAs($student);
    $this->get(route('admin.lessons.preview', lpModuleWithLesson()->id))->assertForbidden();
});
