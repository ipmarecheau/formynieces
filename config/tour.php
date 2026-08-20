<?php

/**
 * The first-run overworld tour (TR-02/03/07) — Smooth walks a new voyager through
 * her Voyage home, one chapter at a time, spotlighting each area. The final chapter
 * is interactive: she taps her first island to sail in, and the tour continues on
 * the island (IslandTour) and then inside a lesson (LessonTour).
 *
 * Each chapter: title, Smooth pose (wave|cheer|chart), a CSS `target` to spotlight
 * (null = a centred card), `interactive` (true = let her tap the spotlit element to
 * continue, no Next button), and ordered child-layer `lines`.
 */
return [

    'title' => 'Your tour of the ship',
    'chapters' => [
        [
            'key' => 'welcome', 'title' => 'Welcome aboard!', 'pose' => 'wave', 'target' => null, 'interactive' => false,
            'lines' => [
                'Ahoy! I’m Smooth, your turtle first mate. 🐢',
                'Let me show you around your ship — tap Next to sail on.',
            ],
        ],
        [
            'key' => 'map', 'title' => 'Your Voyage map', 'pose' => 'chart', 'target' => '.vy-map', 'interactive' => false,
            'lines' => [
                'This is your sea! Every island is a skill, and every stop on it is something to learn.',
                'The glowing islands are where to sail this week.',
            ],
        ],
        [
            'key' => 'legend', 'title' => 'The island list', 'pose' => 'chart', 'target' => '.vy-legend', 'interactive' => false,
            'lines' => [
                'This list names every island and shows which are open, locked, or glowing for this week.',
                'It’s your quick key to the whole map.',
            ],
        ],
        [
            'key' => 'orders', 'title' => 'Captain’s Orders', 'pose' => 'chart', 'target' => '.co-rail', 'interactive' => true,
            'hint' => 'Tap your Orders scroll to unroll it 📜',
            'lines' => [
                'This rolled-up scroll on the left is your Captain’s Orders.',
                'Give it a tap to unroll it and see today’s plan!',
            ],
        ],
        [
            'key' => 'tab-orders', 'title' => 'The Orders tab', 'pose' => 'chart', 'target' => '[data-tour="tab-orders"]', 'show_tab' => 'orders', 'interactive' => false,
            'lines' => [
                'Four tabs live up here — let me walk you through each one. This first is your Orders.',
                'Start with your Morning Tide, then finish the lessons listed for today.',
                'Each one checks off as you master it — that’s how you stay on course for your big exam.',
            ],
        ],
        [
            'key' => 'tab-locker', 'title' => 'The Locker tab', 'pose' => 'cheer', 'target' => '[data-tour="tab-locker"]', 'show_tab' => 'locker', 'interactive' => false,
            'lines' => [
                'Next is your Captain’s Locker — it holds your perks, little helpers that protect your streak.',
                'You already have one of each as a joining gift. 🎁 Tap one any time you need it.',
            ],
        ],
        [
            'key' => 'tab-journal', 'title' => 'The Journal tab', 'pose' => 'chart', 'target' => '[data-tour="tab-journal"]', 'show_tab' => 'journal', 'interactive' => false,
            'lines' => [
                'Your Journal keeps every streak you’re building — reading, vocabulary, writing and the map.',
                'The milestones show how many days in a row you’ve sailed. Keep them alive! 🔥',
            ],
        ],
        [
            'key' => 'tab-logs', 'title' => 'The Logs tab', 'pose' => 'chart', 'target' => '[data-tour="tab-logs"]', 'show_tab' => 'logs', 'interactive' => false,
            'lines' => [
                'Last is your Logs — the day-by-day record of every voyage you’ve completed.',
                'It’s a proud trail of everywhere you’ve sailed. Now let’s go conquer an island!',
            ],
        ],
        [
            'key' => 'sail', 'title' => 'Let’s sail in!', 'pose' => 'cheer', 'target' => '.vy-island.is-current', 'interactive' => true,
            'hint' => 'Tap the glowing island to keep going 👆',
            'lines' => [
                'Ready? Tap this glowing island to sail in and see its stops!',
            ],
        ],
    ],
];
