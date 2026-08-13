<?php

declare(strict_types=1);

namespace App\Support;

/**
 * LessonBlockSchema — the single authority for lesson block types and their shapes.
 *
 * The importer validates against this, the downloadable template is generated from it, and the
 * authoring form / renderer share the same vocabulary (see LessonTemplate::BLOCK_TYPES). Adding a
 * new interaction type means adding it here, and the importer + template pick it up for free.
 */
class LessonBlockSchema
{
    /**
     * type => list of REQUIRED field names. Optional fields (e.g. check.explain, fillblank.options)
     * are allowed but not enforced here.
     *
     * @var array<string, array<int, string>>
     */
    public const REQUIRED = [
        'heading' => ['content'],
        'text' => ['content'],
        'key' => ['content'],
        'example' => ['content'],
        'visual' => ['content'],
        'check' => ['question', 'options', 'answer'],
        'fillblank' => ['prompt', 'answer'],
        'markwords' => ['instruction', 'text'],
        'matchpairs' => ['instruction', 'pairs'],
        'ordersteps' => ['instruction', 'items'],
    ];

    /** @return array<int, string> the known block type names. */
    public static function types(): array
    {
        return array_keys(self::REQUIRED);
    }

    /**
     * Validate one block. Returns a list of human-readable error strings (empty = valid).
     *
     * @return array<int, string>
     */
    public static function validateBlock(mixed $block, int $position): array
    {
        $where = "block #{$position}";

        if (! is_array($block)) {
            return ["{$where}: is not an object"];
        }

        $type = $block['type'] ?? null;
        if (! is_string($type) || ! isset(self::REQUIRED[$type])) {
            return ["{$where}: unknown or missing block type '".(is_string($type) ? $type : 'null')."'"];
        }

        $errors = [];
        foreach (self::REQUIRED[$type] as $field) {
            if (! array_key_exists($field, $block) || $block[$field] === null || $block[$field] === '' || $block[$field] === []) {
                $errors[] = "{$where} ({$type}): missing required field '{$field}'";
            }
        }

        // Type-specific structural checks.
        if ($type === 'check') {
            if (isset($block['options']) && ! array_is_list((array) $block['options'])) {
                $errors[] = "{$where} (check): 'options' must be a list";
            }
            if (isset($block['answer']) && (! is_int($block['answer']) || $block['answer'] < 0 || $block['answer'] >= count((array) ($block['options'] ?? [])))) {
                $errors[] = "{$where} (check): 'answer' must be a 0-based index into 'options'";
            }
        }

        if ($type === 'matchpairs') {
            foreach ((array) ($block['pairs'] ?? []) as $pi => $pair) {
                if (! is_array($pair) || ($pair['left'] ?? '') === '' || ($pair['right'] ?? '') === '') {
                    $errors[] = "{$where} (matchpairs): pair #{$pi} needs a 'left' and a 'right'";
                }
            }
        }

        if (in_array($type, ['ordersteps', 'matchpairs'], true)) {
            $listField = $type === 'ordersteps' ? 'items' : 'pairs';
            if (count((array) ($block[$listField] ?? [])) < 2) {
                $errors[] = "{$where} ({$type}): needs at least 2 {$listField}";
            }
        }

        return $errors;
    }

    /**
     * One example block per type — the body of the downloadable import template (LB-04).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function sampleBlocks(): array
    {
        return [
            ['type' => 'heading', 'content' => 'A section title'],
            ['type' => 'text', 'content' => 'A kid-friendly explanation of the idea.'],
            ['type' => 'key', 'content' => 'The one rule to remember.'],
            ['type' => 'example', 'content' => 'A worked example set-up.', 'steps' => ['Step one', 'Step two', 'The answer']],
            ['type' => 'visual', 'content' => 'https://example.com/diagram.png'],
            ['type' => 'check', 'question' => 'Which is correct?', 'options' => ['Wrong', 'Right'], 'answer' => 1, 'explain' => 'Why the second is right.'],
            ['type' => 'fillblank', 'prompt' => 'The cat ___ on the mat.', 'answer' => 'sat', 'options' => ['sat', 'sit'], 'explain' => 'Past tense of sit.'],
            ['type' => 'markwords', 'instruction' => 'Tap the verb', 'text' => 'The dog *runs* home', 'explain' => 'Runs is the action word.'],
            ['type' => 'matchpairs', 'instruction' => 'Match each word to its meaning', 'pairs' => [['left' => 'big', 'right' => 'large'], ['left' => 'fast', 'right' => 'quick']]],
            ['type' => 'ordersteps', 'instruction' => 'Put the steps in order', 'items' => ['Line up the digits', 'Add the ones', 'Write the answer']],
        ];
    }
}
