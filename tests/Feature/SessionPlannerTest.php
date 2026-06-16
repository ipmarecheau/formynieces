<?php

use App\Services\Diagnostic\SessionPlanner;
use Database\Seeders\ElaAnchorQuestionSeeder;
use Database\Seeders\MathAnchorQuestionSeeder;
use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;
use Database\Seeders\WritingAnchorQuestionSeeder;
use Illuminate\Support\Facades\DB;

/**
 * Step 2a of the diagnostic engine — SessionPlanner.
 *
 * Drives the "plans items per the 50/30/20 paper weighting" scenario from
 * diagnostic.feature, and verifies every slot resolves to a real anchor against
 * the seeded bank (including the thin strands like Percent and Punctuation).
 */

beforeEach(function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);
    $this->seed(MathAnchorQuestionSeeder::class);
    $this->seed(ElaAnchorQuestionSeeder::class);
    $this->seed(WritingAnchorQuestionSeeder::class);

    $this->planner = new SessionPlanner;
});

it('allocates roughly 15 Math anchors with Number weighted heaviest', function () {
    // diagnostic.feature: "plans items per the 50/30/20 paper weighting"
    $plan = $this->planner->buildPlan();
    $math = collect($plan)->where('subject', 'Math');

    expect($math)->toHaveCount(15);

    $byStrand = $math->countBy('strand');
    $numberCount = $byStrand['Number'];
    $otherMax = $byStrand->forget('Number')->max();

    expect($numberCount)->toBeGreaterThan($otherMax);
});

it('splits ELA evenly between Section I and Section II', function () {
    $plan = collect($this->planner->buildPlan());
    $ela = $plan->where('subject', 'ELA');

    $sectionI = ['Spelling', 'Punctuation', 'Capitalisation', 'Grammar'];
    $sectionII = ['Comprehension', 'Poetry', 'Media'];

    $countI = $ela->whereIn('strand', $sectionI)->count();
    $countII = $ela->whereIn('strand', $sectionII)->count();

    expect($countI)->toBe($countII);
});

it('includes a Writing slot for each of the four writing concepts', function () {
    $plan = collect($this->planner->buildPlan());
    expect($plan->where('subject', 'Writing'))->toHaveCount(4);
});

it('produces roughly thirty slots in total', function () {
    $plan = $this->planner->buildPlan();
    expect(count($plan))->toBeGreaterThanOrEqual(28)->toBeLessThanOrEqual(34);
});

it('starts every slot at medium difficulty', function () {
    $plan = $this->planner->buildPlan();
    foreach ($plan as $slot) {
        expect($slot['difficulty'])->toBe(SessionPlanner::DIFFICULTY_MEDIUM);
    }
});

it('interleaves strands rather than front-loading one, as far as allocation allows', function () {
    // Round-robin spreads strands out; the heaviest strand (Number) unavoidably
    // clusters at the TAIL once lighter strands are exhausted. We assert the
    // spread is honest: the first appearance of each Math strand happens before
    // any strand is seen a third time.
    $math = collect($this->planner->buildPlan())->where('subject', 'Math')->values();

    $firstPass = $math->take(8)->pluck('strand')->unique();
    // All 8 Math strands should appear within the first 8 slots (one full round).
    expect($firstPass->count())->toBe(8);
});

it('resolves every slot to a real anchor, including thin strands', function () {
    $plan = $this->planner->buildPlan();
    $used = [];

    foreach ($plan as $slot) {
        $anchorId = $this->planner->resolveSlot($slot, $used);
        expect($anchorId)->not->toBeNull("slot {$slot['subject']}/{$slot['strand']} did not resolve");

        $anchor = DB::table('anchor_questions')->find($anchorId);
        expect($anchor->subject)->toBe($slot['subject']);
        expect($anchor->strand)->toBe($slot['strand']);

        $used[] = $anchorId;
    }

    // No anchor used twice across the whole plan.
    expect(count($used))->toBe(count(array_unique($used)));
});

it('resolves Percent to its hard anchor via nearest-difficulty', function () {
    // Percent has only a difficulty-3 anchor; a medium slot must still resolve.
    $slot = ['subject' => 'Math', 'strand' => 'Percent', 'difficulty' => SessionPlanner::DIFFICULTY_MEDIUM];
    $anchorId = $this->planner->resolveSlot($slot);

    expect($anchorId)->not->toBeNull();
    $anchor = DB::table('anchor_questions')->find($anchorId);
    expect($anchor->strand)->toBe('Percent');
    expect($anchor->difficulty)->toBe(SessionPlanner::DIFFICULTY_HARD); // nearest available
});

it('persists the plan onto the session row', function () {
    $studentId = DB::table('users')->insertGetId([
        'name'       => 'Test Student',
        'email'      => 'planner-test-' . uniqid() . '@example.com',
        'password'   => bcrypt('secret'),
        'role'       => 'student',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sessionId = DB::table('diagnostic_sessions')->insertGetId([
        'student_id'   => $studentId,
        'status'       => 'in_progress',
        'current_item' => 0,
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);

    $plan = $this->planner->planForSession($sessionId);

    $row = DB::table('diagnostic_sessions')->find($sessionId);
    expect(json_decode($row->item_plan, true))->toBe($plan);
    expect($row->current_item)->toBe(0);
});

it('avoids reusing an anchor already used in the session', function () {
    $slot = ['subject' => 'Math', 'strand' => 'Number', 'difficulty' => SessionPlanner::DIFFICULTY_MEDIUM];

    $first = $this->planner->resolveSlot($slot, []);
    $second = $this->planner->resolveSlot($slot, [$first]);

    expect($second)->not->toBeNull()->not->toBe($first);
});
