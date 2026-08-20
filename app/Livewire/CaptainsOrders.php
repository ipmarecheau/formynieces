<?php

namespace App\Livewire;

use App\Models\DailyPlan;
use App\Models\StreakReward;
use App\Models\StudentStreak;
use App\Models\WeeklyTarget;
use App\Services\Motivation\DailyPlanComposer;
use App\Services\Motivation\StreakEconomyService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Captain's Orders — the collapsible Voyage sidebar (CO / SL).
 *
 * Four tabs: Orders (today's brief + this week's goal), Locker (protective
 * rewards), Journal (streaks + milestones), and Logs (the day-by-day record).
 * The student is always resolved from the session, never a URL. No pace deficit,
 * percentage, or weeks-behind figure ever appears here (CO-10 / SL-07); a kind
 * weekly goal count is allowed (CO-11).
 */
class CaptainsOrders extends Component
{
    public bool $collapsed = false;

    public string $tab = 'orders';

    /** Debug-only day override for previewing another weekday (e.g. ?as_of=2026-08-17). */
    public ?string $asOf = null;

    private const TABS = ['orders', 'locker', 'journal', 'logs'];

    private const DUTY_LABELS = [
        'morning_tide' => 'Morning Tide',
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
        'shore_leave' => 'Freeze one streak for one day.',
        'anchor' => 'Freeze all streaks for one day.',
        'tailwind' => 'Do additional work today and get less work tomorrow.',
        'lifebuoy' => 'Did a streak reset? Use this to get another chance and continue the streak.',
    ];

    /** Longer explanations, revealed only when a reward is highlighted. */
    private const REWARD_LONG = [
        'shore_leave' => "Skip one of today's duties and keep your streak safe — as long as you're on course. A well-earned day off from a single job.",
        'anchor' => "Drop anchor and freeze every streak for a whole day. The ultimate safety net for a day you simply can't sail — it always keeps your streaks whole.",
        'tailwind' => 'Catch a tailwind and sail ahead: do a subject twice today to bank up to two days in advance, so a future day can be lighter.',
        'lifebuoy' => 'Lost a streak overboard? Throw a Lifebuoy to bring a just-lost streak back to life — one rescue per slip.',
    ];

    /** SL-08 — how each reward is earned, so an empty Locker is a goal, not a wall. */
    private const REWARD_EARN = [
        'shore_leave' => 'Get ahead of your weekly plan, or reach a streak milestone.',
        'anchor' => 'Reach a big streak milestone — or your captain can grant you one.',
        'tailwind' => 'Sail ahead of your weekly plan two days running.',
        'lifebuoy' => 'Reach a milestone, and keep it spare for a day a streak slips.',
    ];

    private const MILESTONES = [3, 7, 14, 30];

    public function mount(): void
    {
        if (config('app.debug') && ($raw = request()->query('as_of'))) {
            try {
                $this->asOf = Carbon::parse($raw)->toDateString();
            } catch (\Throwable) {
                $this->asOf = null;
            }
        }

        // TR-07: during the overworld tour the orders start rolled up, so opening
        // them is a step of the tour (the student taps the scroll to expand it).
        if (auth()->user()?->tour_stage === 'overworld') {
            $this->collapsed = true;
        }
    }

    public function toggle(): void
    {
        $this->collapsed = ! $this->collapsed;
        // Let the tour advance when the student opens the orders (TR-07).
        $this->dispatch('orders-toggled', collapsed: $this->collapsed);
    }

    /** TR-07 — the tour rolls the orders back up before the island hand-off. */
    #[On('tour-collapse-orders')]
    public function collapseForTour(): void
    {
        $this->collapsed = true;
    }

    public function showTab(string $tab): void
    {
        $this->tab = in_array($tab, self::TABS, true) ? $tab : 'orders';
    }

    /** TR-07 — the tour switches tabs as Smooth walks the student through each one. */
    #[On('tour-show-tab')]
    public function showTabForTour(string $tab): void
    {
        $this->collapsed = false;
        $this->showTab($tab);
    }

    /**
     * Check a thread off for today. The map checks off on its own from practice;
     * this is the affordance for the reading/vocabulary/writing threads whose
     * engines are not built yet (placeholder completion).
     */
    public function completeThread(string $duty): void
    {
        $studentId = (int) auth()->id();
        $on = $this->today();
        app(DailyPlanComposer::class)->markDuty($studentId, $duty, $on);
        app(StreakEconomyService::class)->completeDailyMinimumIfMet($studentId, $on);
    }

    public function useReward(string $type): void
    {
        $studentId = (int) auth()->id();
        if (app(StreakEconomyService::class)->spendReward($studentId, $type)) {
            $this->dispatch('reward-used', type: $type);
        }
    }

    private function today(): Carbon
    {
        return $this->asOf ? Carbon::parse($this->asOf) : Carbon::today();
    }

    public function render(): View
    {
        $studentId = (int) auth()->id();
        $today = $this->today();

        $composer = app(DailyPlanComposer::class);
        $plan = $composer->forDay($studentId, $today);

        // CO-02 — today's paced lessons, as a check-off task list toward the exam.
        $lessonTasks = $composer->todaysLessonTasks($studentId, $today);

        $duties = collect($plan->duties)->map(fn ($done, $key) => [
            'key' => $key,
            'label' => self::DUTY_LABELS[$key] ?? ucfirst($key),
            'done' => (bool) $done,
            'placeholder' => in_array($key, ['writing'], true),
        ])->values();

        // This week's goal + progress — a kind count, never a pace number (CO-11).
        $weekStart = $today->copy()->startOfWeek()->toDateString();
        $targets = WeeklyTarget::where('student_id', $studentId)
            ->where('week_start_date', $weekStart)->get();
        $weeklyGoal = $targets->count();
        $weeklyDone = $targets->filter(fn ($t) => $t->state() === 'completed')->count();

        $streaks = StudentStreak::where('student_id', $studentId)->pluck('count', 'type');
        $voyageStreak = (int) ($streaks['voyage'] ?? 0);

        $subStreaks = collect(self::SUBSTREAKS)->map(fn ($type, $label) => [
            'label' => $label,
            'count' => (int) ($streaks[$type] ?? 0),
        ])->values();

        $rewards = collect(StreakReward::TYPES)->map(fn ($type) => [
            'type' => $type,
            'label' => self::REWARD_LABELS[$type],
            'blurb' => self::REWARD_BLURBS[$type],
            'long' => self::REWARD_LONG[$type],
            'earn' => self::REWARD_EARN[$type],
            'held' => (int) (StreakReward::where('student_id', $studentId)
                ->where('type', $type)->value('quantity') ?? 0),
        ]);

        $milestones = collect(self::MILESTONES)->map(fn ($m) => [
            'days' => $m,
            'reached' => $voyageStreak >= $m,
        ]);

        $logs = DailyPlan::where('student_id', $studentId)
            ->orderByDesc('date')->take(7)->get()
            ->map(fn ($p) => [
                'date' => Carbon::parse($p->date)->format('D j M'),
                'rest' => $p->duties === [],
                'done' => $p->isMinimumMet(),
            ]);

        return view('livewire.captains-orders', [
            'duties' => $duties,
            'lessonTasks' => $lessonTasks,
            'writingDay' => $plan->is_writing_day,
            'writingDone' => (bool) ($plan->duties['writing'] ?? true),
            'isRestDay' => $plan->duties === [],
            'isEvening' => ($this->asOf ? Carbon::parse($this->asOf)->hour : Carbon::now()->hour) >= 17,
            'allDone' => $plan->isMinimumMet(),
            'weeklyGoal' => $weeklyGoal,
            'weeklyDone' => $weeklyDone,
            'weeklyPct' => $weeklyGoal > 0 ? (int) round($weeklyDone / $weeklyGoal * 100) : 0,
            'voyageStreak' => $voyageStreak,
            'subStreaks' => $subStreaks,
            'rewards' => $rewards,
            'milestones' => $milestones,
            'logs' => $logs,
        ]);
    }
}
