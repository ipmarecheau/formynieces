<?php

return [
    // Current Terms & Conditions version a guardian accepts at registration.
    // Bump when the terms change materially (stored on users.terms_version).
    'terms_version' => env('TERMS_VERSION', '2026-08-28'),

    // Current Privacy Policy version. Bump on material changes.
    'privacy_version' => env('PRIVACY_VERSION', '2026-08-28'),

    // Where legal enquiries reach a human.
    'contact_email' => env('LEGAL_CONTACT_EMAIL', 'hello@smoothseas.org'),

    // Governing jurisdiction for these terms.
    'jurisdiction' => 'the Republic of Trinidad and Tobago',
];
