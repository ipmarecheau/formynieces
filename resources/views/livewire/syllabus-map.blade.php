<div>
    <style>
        .sy-wrap { max-width: 920px; margin: 0 auto; padding: 20px 16px 60px; font-family: 'Nunito', sans-serif; }
        .sy-h1 { font-family: 'Fredoka One', cursive; font-size: 1.6rem; color: #0e7490; margin: 0 0 4px; }
        .sy-sub { font-size: 0.9rem; color: #64748b; font-weight: 700; margin: 0 0 14px; }
        .sy-note { background: #fbeecd; border: 1px solid #f2a900; color: #8a5a00; border-radius: 12px; padding: 10px 14px; font-size: 0.82rem; font-weight: 700; margin-bottom: 18px; }
        .sy-card { background: #fff; border: 1.5px solid #e6f2fb; border-radius: 16px; padding: 14px 16px; margin-bottom: 14px; box-shadow: 0 6px 18px rgba(20,34,46,.06); }
        .sy-subject { font-family: 'Fredoka One', cursive; font-size: 1.1rem; color: #0e7490; margin: 8px 2px 4px; }
        .sy-strand { font-family: 'Fredoka One', cursive; font-size: 0.95rem; color: #0a5c68; margin: 0 0 8px; }
        table.sy { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
        table.sy th { text-align: left; font-size: 0.62rem; text-transform: uppercase; letter-spacing: .06em; color: #93b2cc; padding: 6px 8px; border-bottom: 1.5px solid #e6f2fb; }
        table.sy td { padding: 8px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #16242e; }
        .sy-code { font-family: 'IBM Plex Mono', monospace; font-size: 0.72rem; color: #64748b; white-space: nowrap; }
        .sy-kar { display: inline-block; width: 17px; height: 17px; border-radius: 4px; font: 800 0.6rem/17px 'Nunito'; text-align: center; margin-right: 2px; }
        .sy-kar.k { background: #e3f1f2; color: #0a5c68; }
        .sy-kar.a { background: #fbeecd; color: #8a5a00; }
        .sy-kar.r { background: #ece5ff; color: #4b2ea8; }
        .sy-lz { font-size: 0.66rem; font-weight: 800; border-radius: 6px; padding: 2px 8px; white-space: nowrap; }
        .sy-lz.yes { background: #dcefe9; color: #0f766e; }
        .sy-lz.no { background: #f1f5f9; color: #93b2cc; }
        .sy-q { font-family: 'IBM Plex Mono', monospace; font-size: 0.76rem; color: #64748b; }
        .sy-prog { position: relative; height: 8px; border-radius: 999px; background: #eef2f6; overflow: hidden; min-width: 70px; }
        .sy-prog i { position: absolute; inset: 0 auto 0 0; border-radius: 999px; background: linear-gradient(90deg, #0d7d8c, #f2a900); }
        .sy-status { font-size: 0.68rem; font-weight: 800; color: #64748b; display: block; margin-top: 3px; }
        .sy-go { font-size: 0.72rem; font-weight: 800; color: #0e7490; text-decoration: none; white-space: nowrap; }
        .sy-go:hover { text-decoration: underline; }
    </style>

    <div class="sy-wrap">
        <h1 class="sy-h1">Syllabus coverage</h1>
        <p class="sy-sub">
            {{ $isGuardian && $student ? $student->name."'s progress" : 'Your progress' }}
            &middot; {{ $lessonCount }} of {{ $total }} objectives have a lesson
        </p>
        <div class="sy-note">📖 This is a view of content &amp; progress by objective. Lessons open from the <strong>Voyage</strong>, not from here.</div>

        @forelse ($grouped as $subject => $strands)
            <div class="sy-subject">{{ $subject === 'ELA' ? 'English Language Arts' : $subject }}</div>
            @foreach ($strands as $strand => $rows)
                <div class="sy-card">
                    <p class="sy-strand">{{ $strand }}</p>
                    <table class="sy">
                        <thead><tr>
                            <th>Obj</th><th>Objective</th><th>K/A/R</th><th>Lesson</th><th>Qs</th><th>Progress</th>
                            @unless ($isGuardian)<th></th>@endunless
                        </tr></thead>
                        <tbody>
                        @foreach ($rows as $r)
                            <tr>
                                <td class="sy-code">{{ $r['code'] }}</td>
                                <td>{{ $r['topic'] }}</td>
                                <td>@foreach ($r['kar'] as $d)<span class="sy-kar {{ strtolower($d) }}">{{ $d }}</span>@endforeach</td>
                                <td>@if ($r['has_lesson'])<span class="sy-lz yes">lesson</span>@else<span class="sy-lz no">planned</span>@endif</td>
                                <td class="sy-q">{{ $r['questions'] }}</td>
                                <td>
                                    <div class="sy-prog"><i style="width: {{ $r['progress'] }}%"></i></div>
                                    <span class="sy-status">{{ $r['status_label'] }}</span>
                                </td>
                                @unless ($isGuardian)
                                    <td>@if ($r['voyage_url'])<a class="sy-go" href="{{ $r['voyage_url'] }}">On your Voyage →</a>@endif</td>
                                @endunless
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @empty
            <div class="sy-card">No syllabus modules found.</div>
        @endforelse
    </div>
</div>
