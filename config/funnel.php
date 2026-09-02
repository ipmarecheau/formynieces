<?php

return [
    // How many questions the free SEA mock serves (short, just enough to place).
    'mock_questions' => env('FUNNEL_MOCK_QUESTIONS', 8),

    // How many questions the free AI Practice Pack booklet contains (LG-09).
    'pack_questions' => env('FUNNEL_PACK_QUESTIONS', 30),

    // Length of the claimed free trial, in days (LG-07/08) — a full month vs rivals' 7.
    'trial_days' => env('FUNNEL_TRIAL_DAYS', 30),

    // Admin notification (LG-12): all "WhatsApp" traffic routes to the team's own number.
    // Left null → nothing is sent and nothing errors. Set to a number to enable.
    'admin_whatsapp' => env('FUNNEL_ADMIN_WHATSAPP'),
    'admin_email' => env('FUNNEL_ADMIN_EMAIL'),
];
