<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Free-tier gating
    |--------------------------------------------------------------------------
    |
    | When false (the free-launch default), plan gating is dormant: every account
    | has full access exactly as before. When true, accounts on the Free plan are
    | limited to the map and mastery quizzes, and the teaching, rituals, AI and full
    | reporting live behind the upgrade wall (free_tier.feature). Flip this on only
    | once existing accounts have been migrated to a grandfathered paid plan.
    |
    */
    'free_tier' => env('FEATURE_FREE_TIER', false),
];
