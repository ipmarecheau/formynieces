<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Student personas for the granularity audit
    |--------------------------------------------------------------------------
    | The student-simulator agent role-plays each of these learners walking a
    | lesson blind (answers hidden) and asks the questions that learner would
    | genuinely ask. Multiple levels are what surface "any student at any level"
    | granularity gaps. Tune freely — the audit reads this list.
    */
    'personas' => [
        [
            'id' => 'below-level',
            'label' => 'A struggling learner one level below the target',
            'profile' => 'Reads slowly, forgets prior steps, needs every jump spelled out. Gets lost when a term is used before it is defined or when two steps are collapsed into one.',
        ],
        [
            'id' => 'on-level',
            'label' => 'An on-level Standard 4/5 learner in Trinidad & Tobago',
            'profile' => 'Around 10-12 years old, often on a phone. Capable but asks about anything ambiguous, any leap in the worked example, and any word not in everyday Caribbean speech.',
        ],
        [
            'id' => 'esl',
            'label' => 'A learner with low reading fluency / English as a second language',
            'profile' => 'Trips on idioms, long sentences, and vocabulary. Asks what words mean and gets confused by anything not stated plainly.',
        ],
    ],
];
