<x-filament-panels::page>
    <style>
        .lig-toc { display: flex; flex-wrap: wrap; gap: .5rem; margin: .5rem 0 0; }
        .lig-toc a { font-size: .8rem; font-weight: 600; padding: .3rem .7rem; border-radius: 999px; text-decoration: none;
            background: rgba(59,130,246,.12); color: #1d4ed8; border: 1px solid rgba(59,130,246,.25); }
        .dark .lig-toc a { background: rgba(96,165,250,.15); color: #93c5fd; border-color: rgba(96,165,250,.3); }
        .lig-card { border: 1px solid rgba(0,0,0,.1); border-radius: .75rem; padding: 1.1rem 1.25rem; margin-bottom: 1rem; scroll-margin-top: 5rem; }
        .dark .lig-card { border-color: rgba(255,255,255,.1); }
        .lig-card h3 { margin: 0 0 .35rem; font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: .5rem; }
        .lig-card h3 code { font-size: .8rem; padding: .1rem .5rem; border-radius: .4rem; background: rgba(0,0,0,.06); }
        .dark .lig-card h3 code { background: rgba(255,255,255,.1); }
        .lig-desc { margin: 0 0 .6rem; opacity: .85; }
        .lig-fields { font-size: .85rem; margin: 0 0 .6rem; }
        .lig-fields code { padding: .05rem .35rem; border-radius: .3rem; background: rgba(0,0,0,.06); }
        .dark .lig-fields code { background: rgba(255,255,255,.1); }
        .lig-behavior { font-size: .85rem; margin: 0 0 .7rem; padding-left: .75rem; border-left: 3px solid rgba(59,130,246,.5); opacity: .9; }
        .lig-gates { display: inline-block; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
            padding: .1rem .45rem; border-radius: .35rem; background: rgba(217,119,6,.15); color: #b45309; margin-left: .25rem; }
        .dark .lig-gates { background: rgba(251,191,36,.15); color: #fcd34d; }
        .lig-pre { margin: 0; padding: .85rem 1rem; border-radius: .6rem; overflow-x: auto; font-size: .8rem; line-height: 1.5;
            background: #0f172a; color: #e2e8f0; }
        .lig-pre code { background: none; padding: 0; color: inherit; }
        .lig-section-title { font-size: 1.15rem; font-weight: 700; margin: 1.75rem 0 .35rem; }
    </style>

    <div class="prose dark:prose-invert max-w-none">
        {{-- Overview --}}
        <p>
            Bulk-load lessons from one <strong>JSON file</strong>. Each lesson binds to a syllabus module by its
            stable <strong>code</strong> (<code>MATH-001</code>, <code>ELA-001</code>, …). You can describe
            <em>any</em> lesson — any mix of explanation, media and interactive steps, in any order — because a lesson
            is simply an <strong>ordered list of blocks</strong>, and you choose which block types to include and how
            to arrange them.
        </p>
        <ul>
            <li><strong>Upsert by module</strong> — one lesson per module, so re-importing the same file updates it instead of duplicating.</li>
            <li><strong>Preview first</strong> — tick “Preview only” to validate a file and see what would happen, saving nothing.</li>
            <li><strong>All-or-nothing per lesson</strong> — a lesson with any invalid block, or an unknown module code, is skipped and reported. It is never half-saved.</li>
        </ul>
    </div>

    {{-- How to import --}}
    <x-filament::section>
        <x-slot name="heading">How to import</x-slot>
        <div class="prose dark:prose-invert max-w-none">
            <ol>
                <li>Go to <strong>Lessons → Import lessons</strong>.</li>
                <li>Choose your <code>.json</code> file. Tick <strong>Preview only</strong> to check it without saving.</li>
                <li>Read the result — how many were <em>new</em> / <em>updated</em> / <em>skipped</em>, and the reason for any skips.</li>
                <li>Fix anything reported, then import again. Re-importing is safe (it updates, never duplicates).</li>
            </ol>
            <p class="text-sm">Starting out? Use <strong>Download template</strong> (top right) for a ready-to-edit file that uses every block type, or <strong>Export all</strong> on the Lessons list to see your current lessons in this exact format.</p>
        </div>
    </x-filament::section>

    {{-- Lesson shape --}}
    <x-filament::section>
        <x-slot name="heading">The shape of a lesson</x-slot>
        <div class="prose dark:prose-invert max-w-none">
            <p>The file is a list of lessons. Each lesson object has four fields:</p>
            <ul>
                <li><code>module</code> — the module code this lesson belongs to. <strong>Required.</strong></li>
                <li><code>title</code> — the lesson’s title. <strong>Required.</strong></li>
                <li><code>is_published</code> — <code>true</code> to serve it to students, <code>false</code> to keep it a draft. Optional (defaults to <code>true</code>).</li>
                <li><code>blocks</code> — the ordered list of blocks that make up the lesson. <strong>Required, at least one.</strong></li>
            </ul>
            <pre class="lig-pre"><code>[
  {
    "module": "MATH-001",
    "title": "My lesson",
    "is_published": true,
    "blocks": [ /* any mix of the block types below, in any order */ ]
  }
]</code></pre>
        </div>
    </x-filament::section>

    {{-- Block type reference --}}
    <p class="lig-section-title">Block types</p>
    <p style="opacity:.8; margin:.25rem 0 .5rem;">Every block has a <code>type</code> and its own fields. Jump to one:</p>
    <div class="lig-toc">
        @foreach ($this->blockGuide() as $b)
            <a href="#block-{{ $b['type'] }}">{{ $b['label'] }}</a>
        @endforeach
    </div>

    <div style="margin-top:1rem;">
        @foreach ($this->blockGuide() as $b)
            <div class="lig-card" id="block-{{ $b['type'] }}">
                <h3>
                    {{ $b['label'] }} <code>"type": "{{ $b['type'] }}"</code>
                    @if (str_starts_with($b['behavior'], 'GATES'))<span class="lig-gates">gates the lesson</span>@endif
                </h3>
                <p class="lig-desc">{{ $b['description'] }}</p>
                <p class="lig-fields">
                    <strong>Required:</strong>
                    @forelse ($b['required'] as $f)<code>{{ $f }}</code>@if (! $loop->last) @endif @empty <em>none</em> @endforelse
                    &nbsp;·&nbsp;
                    <strong>Optional:</strong>
                    @forelse ($b['optional'] as $f)<code>{{ $f }}</code>@if (! $loop->last) @endif @empty <em>none</em> @endforelse
                </p>
                <p class="lig-behavior">{{ $b['behavior'] }}</p>
                <pre class="lig-pre"><code>{{ $b['example'] }}</code></pre>
            </div>
        @endforeach
    </div>

    {{-- Module codes --}}
    <x-filament::section>
        <x-slot name="heading">Finding module codes</x-slot>
        <div class="prose dark:prose-invert max-w-none">
            <p>
                Every module has a code shaped <code>SUBJECT-NNN</code> — <code>MATH-001</code>…<code>MATH-051</code>
                for Mathematics and <code>ELA-001</code>…<code>ELA-039</code> for English, numbered in syllabus order.
                The code is <strong>stable</strong>: renaming a topic does not change it. See every module and its code
                in the <strong>Syllabus modules</strong> list.
            </p>
        </div>
    </x-filament::section>

    {{-- Full example --}}
    <x-filament::section collapsible collapsed>
        <x-slot name="heading">A full example (every block type)</x-slot>
        <p style="opacity:.8; margin:0 0 .5rem; font-size:.85rem;">This is exactly what the downloadable template contains.</p>
        <pre class="lig-pre"><code>{{ $this->sampleJson() }}</code></pre>
    </x-filament::section>
</x-filament-panels::page>
