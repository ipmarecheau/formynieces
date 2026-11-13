<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Auto-login (impersonation) for the guided test-drive
    |--------------------------------------------------------------------------
    |
    | The /user-testing walkthrough embeds the real screens and signs the tester
    | in as a dedicated, seeded test account for each scenario. That auto-login is
    | powerful, so it is HARD-GATED: it only ever logs into accounts flagged
    | is_test, and it is refused entirely in production unless explicitly enabled.
    |
    */

    'impersonation' => (bool) env(
        'USER_TESTING_IMPERSONATION',
        env('APP_ENV') !== 'production',
    ),

    // Every seeded test account shares this domain and is flagged is_test, so it is
    // isolated from real families and easy to purge.
    'email_domain' => 'testdrive.smoothseas.test',

    'password' => 'testdrive',

    /*
    |--------------------------------------------------------------------------
    | Native dev apps (for device scenarios)
    |--------------------------------------------------------------------------
    |
    | Device features can't be exercised in an embedded web preview, so the
    | walkthrough shows a QR code to sideload the dev APK, then guides the tester
    | through granting permissions and using the app. These are the public dev
    | APK URLs (served un-gated from /apps on the dev host).
    |
    */

    'apps' => [
        'kids' => env('USER_TESTING_APK_KIDS', 'https://dev.smoothseas.org/apps/smoothseas-kids-dev.apk'),
        'parent' => env('USER_TESTING_APK_PARENT', 'https://dev.smoothseas.org/apps/smoothseas-parent-dev.apk'),
    ],
];
