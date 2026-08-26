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
        'numberline' => ['low', 'high'],
        'check' => ['question', 'options', 'answer'],
        'fillblank' => ['prompt', 'answer'],
        'markwords' => ['instruction', 'text'],
        'matchpairs' => ['instruction', 'pairs'],
        'ordersteps' => ['instruction', 'items'],
    ];

    /**
     * type => list of OPTIONAL field names (allowed extras beyond the required ones).
     *
     * @var array<string, array<int, string>>
     */
    public const OPTIONAL = [
        'heading' => [],
        'text' => [],
        'key' => [],
        'example' => ['steps'],
        'visual' => [],
        'numberline' => ['value', 'value2', 'marks', 'halfway', 'question', 'content'],
        'check' => ['explain', 'rule', 'practiceItems'],
        'fillblank' => ['options', 'explain', 'rule', 'practiceItems'],
        'markwords' => ['explain', 'rule', 'practiceItems'],
        'matchpairs' => ['rule', 'practiceItems'],
        'ordersteps' => ['rule', 'practiceItems'],
    ];

    /**
     * Interactive block types that carry re-teach content (`rule` + `practiceItems`) so Smooth can
     * remediate the EXACT rule the block teaches (LL-24). See the lesson authoring guide §3.1.
     *
     * @var array<int, string>
     */
    public const INTERACTIVE = ['check', 'fillblank', 'markwords', 'matchpairs', 'ordersteps'];

    /** @return array<int, string> the known block type names. */
    public static function types(): array
    {
        return array_keys(self::REQUIRED);
    }

    /**
     * Full authoring documentation, one entry per block type — the single source the import guide
     * renders from, so the docs can never drift from what the importer accepts.
     *
     * @return array<int, array{type:string, label:string, description:string, required:array<int,string>, optional:array<int,string>, behavior:string, example:string}>
     */
    public static function guide(): array
    {
        $examples = collect(self::sampleBlocks())->keyBy('type');

        $meta = [
            'heading' => ['Heading', 'A short section title that breaks the lesson into parts.', 'Rendered as a bold sub-heading. Not interactive.'],
            'text' => ['Explanation', 'A paragraph of plain, kid-friendly explanation.', 'Rendered as body text. Not interactive.'],
            'key' => ['Remember-this rule', 'One rule or idea to remember, called out from the flow.', 'Rendered as a highlighted callout. Not interactive.'],
            'example' => ['Worked example', 'A worked example: a set-up plus optional step-by-step lines.', 'Shows the set-up, then each step in "steps". "steps" is an optional list of strings. Not interactive.'],
            'visual' => ['Image', 'An on-platform image, given by URL.', 'Renders the image at "content". Use a full https URL. Not interactive.'],
            'numberline' => ['Number line', 'A number line showing one value between two round numbers, with the halfway mark drawn.', 'Draws "value" between "low" and "high" with the halfway mark. If "question" is set she taps which end the value is closer to (illustrative feedback — does NOT gate the lesson). Optional "content" is a caption shown under the line.'],
            'check' => ['Inline check', 'A multiple-choice question she answers inline.', 'GATES the lesson: she cannot move on until she picks the right option. "options" is the list of choices; "answer" is the 0-based index of the correct one (0 = first). Optional "explain" shows after a correct answer.'],
            'fillblank' => ['Fill in the blank', 'A sentence with a blank she completes.', 'GATES until correct. Put ___ in "prompt" where the blank goes. If "options" (a word bank) is given she taps a word; if omitted she types the answer. Matching is case-insensitive and trims spaces. Optional "explain" shows after a correct answer.'],
            'markwords' => ['Mark the words', 'She taps the target word(s) inside a sentence.', 'GATES until correct. In "text", wrap each target word in *asterisks* (e.g. The dog *runs* home). She must tap exactly the marked words — no more, no fewer. Optional "explain" shows after a correct answer.'],
            'matchpairs' => ['Match pairs', 'She matches each left-hand item to its right-hand partner.', 'GATES until every pair is matched. "pairs" is a list of {left, right} objects (at least 2). Students see the right column shuffled and tap a left, then its right.'],
            'ordersteps' => ['Order the steps', 'She arranges shuffled items into the right sequence.', 'GATES until the order is right. List "items" in the CORRECT order (at least 2); students see them shuffled and reorder with up/down arrows.'],
        ];

        $out = [];
        foreach (self::REQUIRED as $type => $required) {
            [$label, $description, $behavior] = $meta[$type];
            $out[] = [
                'type' => $type,
                'label' => $label,
                'description' => $description,
                'required' => $required,
                'optional' => self::OPTIONAL[$type] ?? [],
                'behavior' => $behavior,
                'example' => (string) json_encode($examples[$type] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ];
        }

        return $out;
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

        if ($type === 'numberline') {
            foreach (['low', 'high', 'value'] as $field) {
                if (isset($block[$field]) && ! is_numeric($block[$field])) {
                    $errors[] = "{$where} (numberline): '{$field}' must be a number";
                }
            }
            if (is_numeric($block['low'] ?? null) && is_numeric($block['high'] ?? null) && $block['low'] >= $block['high']) {
                $errors[] = "{$where} (numberline): 'low' must be less than 'high'";
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
            ['type' => 'numberline', 'low' => 6000, 'high' => 7000, 'value' => 6432, 'question' => 'Which thousand is 6,432 closer to?', 'content' => '6,432 is before halfway (6,500), so it rounds to 6,000.'],
            ['type' => 'check', 'question' => 'Which is correct?', 'options' => ['Wrong', 'Right'], 'answer' => 1, 'explain' => 'Why the second is right.'],
            ['type' => 'fillblank', 'prompt' => 'The cat ___ on the mat.', 'answer' => 'sat', 'options' => ['sat', 'sit'], 'explain' => 'Past tense of sit.'],
            ['type' => 'markwords', 'instruction' => 'Tap the verb', 'text' => 'The dog *runs* home', 'explain' => 'Runs is the action word.'],
            ['type' => 'matchpairs', 'instruction' => 'Match each word to its meaning', 'pairs' => [['left' => 'big', 'right' => 'large'], ['left' => 'fast', 'right' => 'quick']]],
            ['type' => 'ordersteps', 'instruction' => 'Put the steps in order', 'items' => ['Line up the digits', 'Add the ones', 'Write the answer']],
        ];
    }
}
