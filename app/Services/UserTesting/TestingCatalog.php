<?php

namespace App\Services\UserTesting;

/**
 * The plain-English layer over the BDD scenarios: for each feature, a friendly title and a
 * set of scenarios a non-technical tester can actually follow — a goal, numbered steps, a
 * "what you should see", the persona to sign in as, and the screen to land on. Scenario IDs
 * line up with the Gherkin (formynieces-spec/features/*.feature) so a verdict maps to a spec.
 *
 * `persona` drives the seeded auto-login (see ScenarioFixtures): guest | parent | child | admin.
 * `start` is the path the embedded preview opens on, already signed in as that persona.
 *
 * Only features seeded here appear in /user-testing. Device-only features (push, local
 * reminders) are intentionally absent — they can't be exercised in an embedded web preview.
 */
class TestingCatalog
{
    /**
     * @return array<string, array{code: string, name: string, blurb: string, scenarios: array<int, array{id: string, title: string, goal: string, persona: string, start: string, steps: array<int, string>, expect: string}>}>
     */
    public static function features(): array
    {
        return [
            'HC' => [
                'code' => 'HC',
                'name' => 'Asking for help',
                'blurb' => 'A help button on every screen so anyone — a parent, a child, or a visitor — can report a problem or leave a voice note the moment they hit it.',
                'scenarios' => [
                    [
                        'id' => 'HC-01',
                        'title' => 'Help is always one tap away',
                        'goal' => 'Check that the help button is easy to find and reach, without losing what you were doing.',
                        'persona' => 'parent',
                        'start' => '/guardian/dashboard',
                        'steps' => [
                            'You are signed in as a parent on the dashboard.',
                            'Look for a small help button (usually floating near a bottom corner).',
                            'Tap it to open the help panel.',
                            'Close it again — notice the page is exactly as you left it.',
                        ],
                        'expect' => 'The help button was easy to find and reach with your thumb, and opening it did not disturb the page.',
                    ],
                    [
                        'id' => 'HC-02',
                        'title' => 'Typing a problem and sending it',
                        'goal' => 'Check that a parent can describe a problem in writing and send it to the team.',
                        'persona' => 'parent',
                        'start' => '/guardian/dashboard',
                        'steps' => [
                            'Open the help panel from the help button.',
                            'Choose what kind of thing it is (for example: a problem, a question).',
                            'Type a short message, e.g. "The lesson would not load for me."',
                            'Tap send.',
                        ],
                        'expect' => 'You saw a thank-you message telling you the team will look into it.',
                    ],
                    [
                        'id' => 'HC-03',
                        'title' => 'A child can speak the problem instead of typing',
                        'goal' => 'Check that a child who finds typing hard can record a voice note instead.',
                        'persona' => 'child',
                        'start' => '/voyage',
                        'steps' => [
                            'You are signed in as a child on the voyage map.',
                            'Open the help panel and choose to record a voice note (look for a microphone).',
                            'Allow microphone access if the device asks.',
                            'Say a few words, stop the recording, and send it.',
                        ],
                        'expect' => 'The voice note recorded and sent, and you saw a thank-you message.',
                    ],
                    [
                        'id' => 'HC-04',
                        'title' => 'An empty message is gently refused',
                        'goal' => 'Check that sending nothing at all is caught, rather than creating a blank report.',
                        'persona' => 'parent',
                        'start' => '/guardian/dashboard',
                        'steps' => [
                            'Open the help panel.',
                            'Do not type anything and do not record anything.',
                            'Try to tap send.',
                        ],
                        'expect' => 'It asked you to add a message or voice note first, and nothing was sent.',
                    ],
                    [
                        'id' => 'HC-05',
                        'title' => 'A visitor who is not signed in can still ask for help',
                        'goal' => 'Check that help works even before signing in.',
                        'persona' => 'guest',
                        'start' => '/',
                        'steps' => [
                            'This is the public home page — you are NOT signed in.',
                            'Open the help panel from the help button.',
                            'Type a short message and send it.',
                        ],
                        'expect' => 'You could send the message as a visitor and saw a thank-you.',
                    ],
                ],
            ],

            'GD' => [
                'code' => 'GD',
                'name' => 'The parent dashboard',
                'blurb' => 'The one screen a busy parent checks: is this week done, are we on pace, and what should we focus on next.',
                'scenarios' => [
                    [
                        'id' => 'GD-01',
                        'title' => 'The four Sunday questions',
                        'goal' => 'Check the dashboard answers the questions a parent actually has at a glance.',
                        'persona' => 'parent',
                        'start' => '/guardian/dashboard',
                        'steps' => [
                            'You are signed in as a parent, looking at the dashboard.',
                            'Find whether this week\'s target was completed.',
                            'Find where your child sits against the expected pace.',
                            'Find the one concrete recommendation for next week.',
                        ],
                        'expect' => 'You could see, without hunting, whether the week was done, how the pace looks, and one clear next step.',
                    ],
                    [
                        'id' => 'GD-02',
                        'title' => 'An honest breakdown of progress',
                        'goal' => 'Check the progress view groups work into clear, honest buckets.',
                        'persona' => 'parent',
                        'start' => '/guardian/progress',
                        'steps' => [
                            'You are on the progress breakdown page.',
                            'Look at how modules are grouped per subject.',
                            'Notice the buckets: mastered, in review, working on, and upcoming.',
                            'Notice Writing is shown as its own paper with its own track.',
                        ],
                        'expect' => 'The breakdown was clear and honest — nothing felt oversold, and Writing had its own place.',
                    ],
                    [
                        'id' => 'GD-05',
                        'title' => 'No games in the serious numbers',
                        'goal' => 'Check the pace and readiness sections stay calm and factual, with no game-style celebration.',
                        'persona' => 'parent',
                        'start' => '/guardian/dashboard',
                        'steps' => [
                            'You are on the parent dashboard.',
                            'Find the pace and readiness sections.',
                            'Look for any streak counters, badges, or celebration styling there.',
                        ],
                        'expect' => 'The pace and readiness sections were plain and factual — no streaks or celebration styling mixed in.',
                    ],
                ],
            ],

            'AL' => [
                'code' => 'AL',
                'name' => 'A child\'s activity log (team view)',
                'blurb' => 'An admin-only timeline of everything a child has done — practice, mastery, essays, finished days, and help requests — composed from existing records.',
                'scenarios' => [
                    [
                        'id' => 'AL-01',
                        'title' => 'Pick a child and see recent activity',
                        'goal' => 'Check the team can choose a child and see their recent activity, newest first.',
                        'persona' => 'admin',
                        'start' => '/admin/student-logs',
                        'steps' => [
                            'You are in the team admin area, on the Student Logs page.',
                            'Use the picker to choose the test child named "Test Explorer (AL)".',
                            'Look at the timeline that appears.',
                        ],
                        'expect' => 'The child\'s recent activity appeared with the newest items at the top.',
                    ],
                    [
                        'id' => 'AL-02',
                        'title' => 'The timeline spans the whole journey',
                        'goal' => 'Check the log shows every kind of moment, not just one.',
                        'persona' => 'admin',
                        'start' => '/admin/student-logs',
                        'steps' => [
                            'Choose the test child named "Test Explorer (AL)" in the picker.',
                            'Scan down the timeline.',
                            'Look for a practice attempt, a mastered module, an essay, a finished day, and a help request.',
                        ],
                        'expect' => 'Each of those different moments showed up in the timeline with a clear, readable summary.',
                    ],
                    [
                        'id' => 'AL-03',
                        'title' => 'It is team-only',
                        'goal' => 'Check the log lives inside the team admin area and is not something a family can reach.',
                        'persona' => 'admin',
                        'start' => '/admin/student-logs',
                        'steps' => [
                            'Notice this page sits inside the team admin area (the /admin section).',
                            'Confirm it reads like a view of existing records, not a new form that collects anything.',
                        ],
                        'expect' => 'It was clearly an internal team view, read-only, with nothing new being collected.',
                    ],
                ],
            ],

            'LR' => [
                'code' => 'LR',
                'name' => 'Reminders on the kids app',
                'blurb' => 'The kids app quietly reminds a child to do their work — even offline, with the app closed. This one needs the app installed on a phone or tablet.',
                'device' => [
                    'app' => 'kids',
                    'intro' => 'These reminders live on the device itself, so you need the kids app installed to try them. Scan the code to install it — you only have to do this once.',
                    'permissions' => [
                        ['icon' => '🔔', 'title' => 'Allow notifications', 'how' => 'When the app first asks, tap Allow. (If you missed it: phone Settings → Apps → SmoothSeas Kids → Notifications → On.)'],
                        ['icon' => '⏰', 'title' => 'Allow alarms & reminders', 'how' => 'On Android 12+, Settings → Apps → SmoothSeas Kids → Alarms & reminders → Allow, so reminders fire exactly on time.'],
                    ],
                ],
                'scenarios' => [
                    [
                        'id' => 'LR-01',
                        'title' => 'The app sets up your week of reminders on first open',
                        'goal' => 'Check the kids app installs and starts cleanly with notifications allowed.',
                        'steps' => [
                            'Scan the code above and install the kids app (allow installing from this source if asked).',
                            'Open the app and sign in using the test child login shown on the left.',
                            'When it asks to send notifications, tap Allow.',
                            'Let the app finish loading to the voyage map.',
                        ],
                        'expect' => 'The app installed, you allowed notifications, and it opened to the map without any error.',
                    ],
                    [
                        'id' => 'LR-07',
                        'title' => 'A reminder appears even with the app closed',
                        'goal' => 'Check a reminder notification shows up later without the app being open.',
                        'steps' => [
                            'With the app installed and notifications allowed, fully close the app.',
                            'Leave the phone as you normally would.',
                            'At the next reminder time (afternoon/evening), watch for a SmoothSeas notification.',
                            'Tap it to confirm it opens the app.',
                        ],
                        'expect' => 'A reminder notification appeared on its own and opened the app when tapped.',
                    ],
                ],
            ],

            'PN' => [
                'code' => 'PN',
                'name' => 'Notifications to your phone',
                'blurb' => 'Push alerts to a parent or child device — e.g. “your child’s essay has been marked”. Needs the app installed and the push server switched on.',
                'device' => [
                    'app' => 'parent',
                    'intro' => 'Push alerts go to the real device, so install the parent app to try them. Scan to install — once only.',
                    'permissions' => [
                        ['icon' => '🔔', 'title' => 'Allow notifications', 'how' => 'When the app first asks, tap Allow. (Settings → Apps → SmoothSeas Parent → Notifications → On if you missed it.)'],
                    ],
                ],
                'scenarios' => [
                    [
                        'id' => 'PN-01',
                        'title' => 'Your device registers for alerts',
                        'goal' => 'Check the parent app installs and registers for notifications when you allow them.',
                        'steps' => [
                            'Scan the code above and install the parent app.',
                            'Open it and sign in using the test parent login shown on the left.',
                            'When it asks to send notifications, tap Allow.',
                        ],
                        'expect' => 'The app installed and accepted notification permission without error.',
                        'note' => 'Delivery of live alerts (essay marked, milestones) opens for testing once the push server is switched on — this step just confirms the device registers.',
                    ],
                ],
            ],
        ];
    }

    public static function feature(string $code): ?array
    {
        return static::features()[strtoupper($code)] ?? null;
    }

    /**
     * True when a feature is tested on a physical device (install the app) rather than in
     * the embedded web preview.
     */
    public static function isDevice(array $feature): bool
    {
        return isset($feature['device']);
    }

    /**
     * Find one scenario by id, with its feature code attached.
     *
     * @return array{id: string, title: string, goal: string, persona: string, start: string, steps: array<int, string>, expect: string, feature: string}|null
     */
    public static function scenario(string $id): ?array
    {
        $id = strtoupper($id);

        foreach (static::features() as $code => $feature) {
            foreach ($feature['scenarios'] as $scenario) {
                if ($scenario['id'] === $id) {
                    return $scenario + ['feature' => $code];
                }
            }
        }

        return null;
    }
}
