<?php

namespace App\Livewire;

use App\Models\StreakReward;
use App\Models\StudentStreak;
use App\Services\Motivation\DailyPlanComposer;
use App\Services\Motivation\StreakEconomyService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Component;

/**
 * Captain's Orders — the collapsible Voyage sidebar (CO / SL).
 *
 * Two tabs: the Captain's Brief (today's minimum duties as a checklist, morning
 * and evening) and the Ship's Log (streaks + the Captain's Locker of rewards).
 * The student is always resolved from the session, never a URL. Nothing from the
 * guardian's honest layer or a pace number ever appears here (CO-10 / SL-07).
 */
class CaptainsOrders extends Component
{
    public bool $collapsed = false;

    public string $tab = 'brief';

    private const DUTY_LABELS = [
        'vocabulary' => 'Vocabulary check',
        'reading' => 'Reading voyage',
        'map' => 'Sail the map',
        'writing' => "Ship's writing",
    ];

    /** Sub-streak display: label => the StudentStreak type it reads. */
    private const SUBSTREAKS = [
        'Reading' => 'reading',
        'Vocabulary' => 'vocabulary',
        'Writing' => 'writing',
        'Map' => 'mastery',
    ];

    private const REWARD_LABELS = [
        'shore_leave' => 'Shore Leave',
        'anchor' => 'Anchor',
        'tailwind' => 'Tailwind',
        'lifebuoy' => 'Lifebuoy',
    ];

    private const REWARD_BLURBS = [
        'shore_leave' => 'Take the day off one duty.',
        'anchor' => 'Hold every streak steady for a day.',
        'tailwind' => 'Sail a day ahead on a subject.',
        'lifebuoy' => 'Bring a just-lost streak back.',
    ];

    public function toggle(): void
    {
        $this->collapsed = ! $this->collapsed;
    }

    public function showTab(string $tab): void
    {
        $this->tab = in_array($tab, ['brief', 'log'], true) ? $tab : 'brief';
    }

    /**
     * Check a thread off for today. The map checks off on its own from practice;
     * this is the affordance for the reading/vocabulary/writing threads whose
     * engines are not built yet (placeholder completion).
     */
    public function completeThread(string $duty): void
    {
        $studentId = (int) auth()->id();
        app(DailyPlanComposer::class)->markDuty($studentId, $duty);
        app(StreakEconomyService::class)->completeDailyMinimumIfMet($studentId);
    }

    public function useReward(string $type): void
    {
        $studentId = (int) auth()->id();
        if (app(StreakEconomyService::class)->spendReward($studentId, $type)) {
            $this->dispatch('reward-used', type: $type);
        }
    }

    public function render(): View
    {
        $studentId = (int) auth()->id();

        $plan = app(DailyPlanComposer::class)->forDay($studentId);

        $duties = collect($plan->duties)->map(fn ($done, $key) => [
            'key' => $key,
            'label' => self::DUTY_LABELS[$key] ?? ucfirst($key),
            'done' => (bool) $done,
            'placeholder' => in_array($key, ['vocabulary', 'reading', 'writing'], true),
        ])->values();

        $streaks = StudentStreak::where('student_id', $studentId)->pluck('count', 'type');

        $subStreaks = collect(self::SUBSTREAKS)->map(fn ($type, $label) => [
            'label' => $label,
            'count' => (int) ($streaks[$type] ?? 0),
        ])->values();

        $rewards = collect(StreakReward::TYPES)->map(fn ($type) => [
            'type' => $type,
            'label' => self::REWARD_LABELS[$type],
            'blurb' => self::REWARD_BLURBS[$type],
            'held' => (int) (StreakReward::where('student_id', $studentId)
                ->where('type', $type)->value('quantity') ?? 0),
        ]);

        return view('livewire.captains-orders', [
            'duties' => $duties,
            'writingDay' => $plan->is_writing_day,
            'writingDone' => (bool) ($plan->duties['writing'] ?? true),
            'isRestDay' => $plan->duties === [],
            'isEvening' => Carbon::now()->hour >= 17,
            'allDone' => $plan->isMinimumMet(),
            'voyageStreak' => (int) ($streaks['voyage'] ?? 0),
            'subStreaks' => $subStreaks,
            'rewards' => $rewards,
        ]);
    }
}
