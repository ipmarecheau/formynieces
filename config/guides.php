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

    'lesson' => [
        'title' => 'Your lesson',
        'pose' => 'wave',
        'lines' => [
            'Read the lesson to learn the idea before you practise.',
            'The links are extra help you can explore if you want them.',
            'When you’re ready, tap “Start practising” to give it a go! 📖',
        ],
    ],

    'writing' => [
        'title' => 'Your Writer’s Log',
        'pose' => 'wave',
        'lines' => [
            'This is your weekly writing challenge — take your time and do your best.',
            'When you send it, you’ll get warm feedback from Smooth’s crew.',
            'You’ll hear two things you did well and one to try next — never a grade. ✍️',
        ],
    ],

    // Shown as an ALERT when a mastered level has fallen due for review (LL-25).
    'review' => [
        'title' => 'A level needs a quick review! ⭐',
        'pose' => 'chart',
        'lines' => [
            'One of your mastered levels is glowing red on the map — keep it sharp before your star slips.',
            'Tap the red, pulsing stop to open its quick re-check.',
            'Answer three tricky questions right on the first try, and your star is safe for another two weeks!',
        ],
    ],

];
