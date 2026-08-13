<?php

declare(strict_types=1);

namespace App\Support;

/**
 * LessonTemplate — the reusable format for authoring interactive lessons (LE-01), for any
 * ELA or Math topic. A lesson is an ordered list of BLOCKS; the renderer (LessonWalk)
 * reveals them one at a time, gating on `check` blocks (retrieval practice).
 *
 * Block schema (each block is an array with a `type`):
 *   heading: ['type' => 'heading', 'content' => string]                  a section title
 *   text:    ['type' => 'text',    'content' => string]                  kid-voice explanation
 *   key:     ['type' => 'key',     'content' => string]                  a "Remember this" rule
 *   example: ['type' => 'example', 'content' => string, 'steps' => string[]]  a worked example
 *   check:   ['type' => 'check', 'question' => string, 'options' => string[],
 *             'answer' => int, 'explain' => string]                      an inline self-check
 *   visual:  ['type' => 'visual', 'content' => string(url)]              an on-platform image
 *   fillblank:  ['type' => 'fillblank', 'prompt' => string(with ___), 'answer' => string,
 *                'options' => string[]?, 'explain' => string?]           fill in the blank (LE-07)
 *   markwords:  ['type' => 'markwords', 'instruction' => string,
 *                'text' => string(with *targets*), 'explain' => string?] tap the target words (LE-08)
 *   matchpairs: ['type' => 'matchpairs', 'instruction' => string,
 *                'pairs' => [['left'=>string,'right'=>string], ...]]     tap-to-match pairs (LE-09)
 *   ordersteps: ['type' => 'ordersteps', 'instruction' => string,
 *                'items' => string[] (in correct order)]                 order the steps (LE-10)
 *
 * Pedagogy baked into scaffold(): gradual release — hook → rule → worked example → check →
 * extend → check → wrap — so every topic teaches the same reliable way.
 */
class LessonTemplate
{
    /** The block types the lesson renderer understands. */
    public const BLOCK_TYPES = ['heading', 'text', 'key', 'example', 'check', 'visual', 'fillblank', 'markwords', 'matchpairs', 'ordersteps'];

    /**
     * A ready-to-fill skeleton for a new lesson. Copy it, replace every [bracketed]
     * placeholder with real, curriculum-aligned content for the topic, and store it as the
     * module's Lesson (title + blocks).
     *
     * @return array{title:string, blocks:array<int,array<string,mixed>>}
     */
    public static function scaffold(string $title, string $topic): array
    {
        return [
            'title' => $title,
            'blocks' => [
                ['type' => 'text', 'content' => "[Hook: one or two friendly sentences introducing {$topic} and why it is useful.]"],
                ['type' => 'key', 'content' => '[The core rule or idea, stated in one simple sentence.]'],
                ['type' => 'example', 'content' => '[Set up one worked example that uses the rule.]', 'steps' => [
                    '[Step 1 — what to notice first.]',
                    '[Step 2 — apply the rule.]',
                    '[Step 3 — the answer.]',
                ]],
                ['type' => 'check', 'question' => '[A quick question that applies the rule.]', 'options' => [
                    '[a plausible wrong option]', '[the correct option]', '[another wrong option]',
                ], 'answer' => 1, 'explain' => '[One line: why that answer is right.]'],
                ['type' => 'heading', 'content' => '[A second idea, or a twist on the rule.]'],
                ['type' => 'text', 'content' => '[Explain the second idea in kid-friendly language, with an example woven in.]'],
                ['type' => 'check', 'question' => '[A check on the second idea.]', 'options' => [
                    '[option A]', '[option B]',
                ], 'answer' => 0, 'explain' => '[One line confirmation.]'],
                ['type' => 'text', 'content' => '[Warm wrap-up: you have the tricks now — time to practise!]'],
            ],
        ];
    }
}
