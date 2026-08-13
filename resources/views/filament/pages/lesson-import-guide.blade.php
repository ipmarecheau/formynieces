<x-filament-panels::page>
    <div class="prose dark:prose-invert max-w-none">
        <p>
            Bulk-import lessons from a <strong>JSON file</strong>. Each lesson binds to a syllabus module by
            its stable <strong>code</strong> (e.g. <code>MATH-001</code>, <code>ELA-001</code>). Importing is an
            <strong>upsert by module</strong>: one lesson per module, so re-importing the same file
            <em>updates</em> rather than duplicating. A lesson with any invalid block or an unknown module code
            is <strong>skipped and reported</strong>, never half-saved.
        </p>

        <h3>How to import</h3>
        <ol>
            <li>Go to <strong>Lessons → Import lessons</strong>.</li>
            <li>Upload your <code>.json</code> file. Tick <strong>Preview only</strong> to validate without saving.</li>
            <li>Read the result: how many were new / updated / skipped, and why any were skipped.</li>
        </ol>
        <p>Not sure of the shape? Use <strong>Download template</strong> (top right) for a ready-to-edit example, or <strong>Export all</strong> from the Lessons list to see your existing lessons in the same format.</p>

        <h3>File shape</h3>
        <p>A file is a list of lessons. Each lesson has:</p>
        <ul>
            <li><code>module</code> — the module code the lesson belongs to (required).</li>
            <li><code>title</code> — the lesson title (required).</li>
            <li><code>is_published</code> — <code>true</code> to serve it to students, <code>false</code> to keep it as a draft.</li>
            <li><code>blocks</code> — the ordered list of interaction blocks (required, at least one).</li>
        </ul>

        <h3>Block types</h3>
        <p>Every block has a <code>type</code> plus the fields below. Optional extras (like <code>explain</code> on a check, or <code>options</code> on a fill-in-the-blank) are allowed too.</p>
        <table>
            <thead><tr><th>type</th><th>required fields</th></tr></thead>
            <tbody>
                @foreach ($this->blockTypes() as $type => $required)
                    <tr>
                        <td><code>{{ $type }}</code></td>
                        <td>@foreach ($required as $field)<code>{{ $field }}</code>@if (! $loop->last), @endif @endforeach</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h3>Finding module codes</h3>
        <p>
            Each module has a code shaped <code>SUBJECT-NNN</code> — <code>MATH-001</code>…<code>MATH-051</code> for
            Mathematics and <code>ELA-001</code>…<code>ELA-039</code> for English, numbered in syllabus order. The
            code is stable: it does not change if a topic is renamed. You can see every module and its code in the
            <strong>Syllabus modules</strong> list.
        </p>

        <h3>A full example</h3>
        <p>This is exactly what the template contains — one lesson using every block type:</p>
        <pre style="white-space: pre-wrap; overflow-x: auto;"><code>{{ $this->sampleJson() }}</code></pre>
    </div>
</x-filament-panels::page>
