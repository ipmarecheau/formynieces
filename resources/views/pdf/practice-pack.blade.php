<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #12222e; font-size: 12px; line-height: 1.5; margin: 0; }
        .cover { text-align: center; padding: 60px 30px 30px; }
        .cover .anchor { font-size: 34px; color: #0d7d8c; }
        .cover h1 { font-size: 26px; margin: 14px 0 6px; color: #0a5c68; }
        .cover p { color: #40566a; margin: 0; }
        .cover .badge { display: inline-block; margin-top: 16px; font-size: 11px; font-weight: bold; color: #0a5c68; background: #fdf1d6; border: 1px solid #f2d69a; border-radius: 20px; padding: 6px 14px; }
        .q { padding: 12px 30px; border-bottom: 1px solid #e7ddcd; page-break-inside: avoid; }
        .q .n { font-weight: bold; color: #0d7d8c; }
        .q .prompt { font-weight: bold; margin: 3px 0 8px; }
        .q .opt { margin: 2px 0; padding-left: 14px; }
        .q .ans { margin-top: 7px; color: #0a5c68; font-weight: bold; }
        .q .sol { margin-top: 3px; color: #40566a; }
        .sec-head { padding: 20px 30px 4px; font-weight: bold; font-size: 14px; color: #0a5c68; }
    </style>
</head>
<body>
    <div class="cover">
        <div class="anchor">&#9875;</div>
        <h1>SmoothSeas SEA Practice Pack</h1>
        <p>{{ $questions->count() }} past-paper-style questions with worked solutions{{ $childLevel ? ' · '.$childLevel : '' }}.</p>
        <div class="badge">Built for the T&amp;T SEA syllabus · SEA 2027</div>
    </div>

    <div class="sec-head">Questions &amp; worked solutions</div>
    @foreach ($questions as $i => $q)
        @php $options = array_values($q->options ?? []); $correct = (int) $q->correct_index; @endphp
        <div class="q">
            <div><span class="n">Q{{ $i + 1 }}.</span> <span class="prompt">{{ strip_tags((string) $q->prompt) }}</span></div>
            @foreach ($options as $oi => $opt)
                <div class="opt">{{ chr(65 + $oi) }}) {{ $opt }}</div>
            @endforeach
            <div class="ans">Answer: {{ chr(65 + $correct) }}) {{ $options[$correct] ?? '' }}</div>
            @if ($q->explanation)
                <div class="sol">Why: {{ strip_tags((string) $q->explanation) }}</div>
            @endif
        </div>
    @endforeach
</body>
</html>
