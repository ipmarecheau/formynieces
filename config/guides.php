<?php

/**
 * Content for Smooth's guide — the contextual how-to shown on each student screen
 * (SG-01..05). Keyed by screen. Copy is child-layer ONLY: it explains what to do,
 * never pace, percentages, targets, or any guardian-gauge metric.
 *
 * Each guide: a title, a Smooth pose (wave|cheer|chart), and ordered lines.
 */
return [

    'practice' => [
        'title' => 'How practice works',
        'pose' => 'chart',
        'lines' => [
            'You climb three levels — Level 1, then 2, then 3 — getting a little trickier each time.',
            'Every question gives you two tries, so a slip is never the end — have another go!',
            'To master a stop, get three right on your FIRST try at the top level. You’ve got this! 🐢',
        ],
    ],

    'voyage' => [
        'title' => 'How to sail your Voyage',
        'pose' => 'chart',
        'lines' => [
            'This is your sea! Each island is a skill to explore.',
            'The glowing islands are where to sail this week — tap one to start.',
            'Finish a stop’s loop to unlock the next island along the trail. Onward! 🗺️',
        ],
    ],

    'island' => [
        'title' => 'Exploring an island',
        'pose' => 'chart',
        'lines' => [
            'Each stop on this island is a level to conquer — follow the trail in order.',
            'Tap the next lit-up stop to open its lesson and practice.',
            'Clear every stop to master the whole island. One step at a time! 🏝️',
        ],
    ],

];
