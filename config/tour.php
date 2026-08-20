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
            'key' => 'tasks', 'title' => 'Today’s tasks', 'pose' => 'chart', 'target' => '.co-frame', 'interactive' => false,
            'lines' => [
                'Here it is! Start with your Morning Tide, then finish the lessons listed for today.',
                'Each one checks off as you master it — that’s how you stay on course for your big exam.',
                'The tabs up top hold your Locker of perks, your Journal of streaks, and your Logs — tap any to peek.',
            ],
        ],
        [
            'key' => 'locker', 'title' => 'Your Captain’s Locker', 'pose' => 'cheer', 'target' => '.co-frame', 'interactive' => false,
            'lines' => [
                'Your Locker holds your perks — little helpers that protect your streak.',
                'You already have one of each as a joining gift. 🎁',
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
