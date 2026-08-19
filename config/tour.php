<?php

/**
 * The first-run application tour (TR-02/03) — Smooth walks a new voyager through
 * what she does each day, one chapter at a time, guided from her Voyage home.
 *
 * Each chapter: a title, a Smooth pose (wave|cheer|chart), a CSS `target` on the
 * Voyage overworld to spotlight (null = a centred card, no spotlight), and ordered
 * child-layer `lines` — never pace, percentages, or targets.
 */
return [

    'title' => 'Your tour of the ship',
    'chapters' => [
        [
            'key' => 'welcome',
            'title' => 'Welcome aboard!',
            'pose' => 'wave',
            'target' => null,
            'lines' => [
                'Ahoy! I’m Smooth, your turtle first mate. 🐢',
                'Let me show you around your ship in a few quick stops — tap Next to sail on.',
            ],
        ],
        [
            'key' => 'map',
            'title' => 'Your Voyage map',
            'pose' => 'chart',
            'target' => '.vy-map',
            'lines' => [
                'This is your sea! Every island is a skill, and every stop on it is something to learn.',
                'The glowing islands are where to sail this week — tap one to start exploring.',
            ],
        ],
        [
            'key' => 'orders',
            'title' => 'Captain’s Orders',
            'pose' => 'chart',
            'target' => '.co-frame',
            'lines' => [
                'These are your orders for today — your daily minimum, as a simple checklist.',
                'Do them in any order you like. Finish them to keep your streak sailing!',
            ],
        ],
        [
            'key' => 'morning_tide',
            'title' => 'Morning Tide',
            'pose' => 'wave',
            'target' => '.co-frame',
            'lines' => [
                'Each morning starts with your Morning Tide: a short reading, a few questions, then some new words.',
                'It’s made to fit in your pocket — perfect for the ride to school.',
            ],
        ],
        [
            'key' => 'learning',
            'title' => 'Learning a stop',
            'pose' => 'chart',
            'target' => '.vy-map',
            'lines' => [
                'Tap a stop and you’ll get a lesson, some worked examples, then practice.',
                'Clear the check at the top and that stop is mastered — the next one opens up!',
            ],
        ],
        [
            'key' => 'locker',
            'title' => 'Your Captain’s Locker',
            'pose' => 'cheer',
            'target' => '.co-frame',
            'lines' => [
                'Open the Locker tab to find your perks — little helpers that protect your streak.',
                'You already have one of each as a joining gift. Use them whenever you need! 🎁',
            ],
        ],
        [
            'key' => 'setsail',
            'title' => 'Ready to sail!',
            'pose' => 'cheer',
            'target' => null,
            'lines' => [
                'That’s the whole ship! You can replay this tour any time from the “Take the tour” button.',
                'Now — let’s go start your first Morning Tide. Onward, Captain! ⛵',
            ],
        ],
    ],
];
