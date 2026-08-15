<x-filament-panels::page>
    <style>
        .lcg-pre { background: rgba(0,0,0,.35); border-radius: .6rem; padding: 1rem; overflow-x: auto; font-size: .82rem; line-height: 1.5; }
        .lcg-step { font-weight: 700; }
        .lcg-num { display: inline-flex; align-items: center; justify-content: center; width: 1.6rem; height: 1.6rem; border-radius: 999px; background: rgb(251 191 36 / .2); color: rgb(217 119 6); font-weight: 700; margin-right: .5rem; }
    </style>

    <x-filament::section>
        <x-slot name="heading">What this is</x-slot>
        <div class="prose dark:prose-invert max-w-none">
            <p>Turn your own source material — a <strong>textbook chapter</strong> or a set of <strong>past-paper
            questions</strong> — into a coherent interactive lesson <em>and</em> its practice-question bank, using
            <strong>Claude Code</strong>, then import them here. Claude follows the project's built-in
            <code>lesson-authoring</code> skill, so the lesson is built for the learning loop and the AI-assisted
            re-teach out of the box.</p>
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">The workflow, step by step</x-slot>
        <div class="prose dark:prose-invert max-w-none">
            <p><span class="lcg-num">1</span><span class="lcg-step">Gather material for ONE skill.</span> One
            textbook section, or past-paper items, that teach a single rule (e.g. <em>plurals: consonant + y → ies</em>).
            Keep it to one skill — a lesson that mixes rules confuses the re-teach.</p>

            <p><span class="lcg-num">2</span><span class="lcg-step">Open Claude Code in the project</span> and
            <strong>attach the material</strong> (drag in the PDF pages, a photo of the page, or paste the text /
            past-paper questions).</p>

            <p><span class="lcg-num">3</span><span class="lcg-step">Ask it to generate, citing the skill.</span> For example:</p>
            <pre class="lcg-pre"><code>Using the lesson-authoring skill, create a lesson for ELA-001
— "Plurals: consonant + y → ies" — from the attached pages.

Produce:
1. one lesson JSON bundle (module, title, is_published, blocks) —
   every interactive block MUST have `rule` + at least 4 same-rule
   `practiceItems` {prompt, answer};
2. a practice-question import with at least {{ $minQuestionsPerLevel }}
   questions at EACH difficulty (D1, D3, D5).

Keep it coherent (one rule only) and run the skill's pre-publish checklist.</code></pre>

            <p><span class="lcg-num">4</span><span class="lcg-step">Vet what it produced.</span> Read the lesson and
            spot-check answers — Claude drafts, you approve. Confirm every interactive block has a <code>rule</code> and
            same-rule <code>practiceItems</code>, and that there are ≥ {{ $minQuestionsPerLevel }} questions per level.
            Explanations should be just the rule + answer — <strong>no “Topic:/Difficulty:” prefixes</strong>.</p>

            <p><span class="lcg-num">5</span><span class="lcg-step">Import.</span> Bring the lesson in via the
            <a href="{{ \App\Filament\Pages\LessonImportGuide::getUrl() }}">Lesson import guide</a> (use
            <strong>Preview only</strong> first), and the questions via <a href="{{ \App\Filament\Pages\ImportQuestions::getUrl() }}">Import questions</a>.</p>
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">What makes a lesson work with the re-teach</x-slot>
        <div class="prose dark:prose-invert max-w-none">
            <p><strong>Coherence is everything.</strong> One skill per lesson; every block teaches it. When a child is
            pulled into a re-teach, Smooth remediates the <em>exact block she missed</em> using that block's
            <code>rule</code> and <code>practiceItems</code> — so those fields must follow the block's own rule, never
            a different one.</p>
            <p>Each interactive block (check, fill-blank, mark-words, match-pairs, order-steps) carries:</p>
            <pre class="lcg-pre"><code>{
  "type": "check",
  "question": "What is the plural of 'city'?",
  "options": ["citys", "cities", "cityes"],
  "answer": 1,
  "explain": "t is a consonant, so change y to i and add es: cities.",
  "rule": "If a word ends in a consonant then y, change the y to i and add es.",
  "practiceItems": [
    { "prompt": "the plural of 'baby'",  "answer": "babies" },
    { "prompt": "the plural of 'lady'",  "answer": "ladies" },
    { "prompt": "the plural of 'penny'", "answer": "pennies" },
    { "prompt": "the plural of 'story'", "answer": "stories" }
  ]
}</code></pre>
            <p>The re-teach spends these across remediation, the end-of-lesson review, and the proof — all
            type-the-answer, all the same rule. <strong>Author at least 4 per interactive block.</strong></p>
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">How many practice questions? (minimum {{ $minQuestionsPerLevel }} per level)</x-slot>
        <div class="prose dark:prose-invert max-w-none">
            <p>Practice never repeats a question (content-hash no-repeat), so each level needs a deep pool. The floor is
            <strong>{{ $minQuestionsPerLevel }} questions at each of D1, D3 and D5</strong> ({{ $minQuestionsPerLevel * 3 }}+ per
            topic); 20+ per level is better. Too few and a child exhausts the level too soon — when that happens the loop
            advances her to the next level with content (routing her through a re-teach first if she was struggling), and
            a thin pool makes that fire far too early.</p>
        </div>
    </x-filament::section>
</x-filament-panels::page>
