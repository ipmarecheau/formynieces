<div>
<style>
    .lw-wrap { min-height: 100vh; display: flex; flex-direction: column; align-items: center; padding: 32px 20px 48px; }
    .lw-subject { font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(196,181,253,0.7); margin-bottom: 8px; }
    .lw-topic { font-family: 'Fredoka One', cursive; font-size: 26px; color: #f3e8ff; text-align: center; margin-bottom: 26px; }
    .lw-card { background: #1a0d30; border: 1.5px solid rgba(147,51,234,0.35); border-radius: 24px; padding: 36px 30px; width: 100%; max-width: 600px; animation: lwFade 0.4s ease both; }
    @keyframes lwFade { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    .lw-section-head { font-family: 'Fredoka One', cursive; font-size: 16px; color: #c084fc; margin: 0 0 12px; }
    .lw-description { font-size: 17px; line-height: 1.7; color: rgba(243,232,255,0.92); margin-bottom: 28px; }
    .lw-resources { list-style: none; padding: 0; margin: 0 0 30px; display: flex; flex-direction: column; gap: 12px; }
    .lw-resource { background: rgba(255,255,255,0.05); border: 1.5px solid rgba(147,51,234,0.3); border-radius: 14px; padding: 14px 18px; }
    .lw-resource a, .lw-resource span { color: #f3e8ff; font-size: 16px; font-weight: 600; text-decoration: none; }
    .lw-resource a:hover { color: #f0abfc; text-decoration: underline; }
    .lw-no-resources { font-size: 15px; color: rgba(196,181,253,0.6); margin-bottom: 30px; }
    .lw-start { display: block; margin: 0 auto; background: linear-gradient(135deg, #9333ea, #db2777); border: none; border-radius: 999px; padding: 16px 38px; color: white; font-family: 'Fredoka One', cursive; font-size: 17px; cursor: pointer; text-decoration: none; text-align: center; max-width: 280px; }
    @media (prefers-reduced-motion: reduce) { .lw-card { transition: none; animation: none; } }
</style>

<div class="lw-wrap">
    <p class="lw-subject">{{ $subject }}</p>
    <p class="lw-topic">{{ $topic }}</p>

    <div class="lw-card">
        @if ($description)
            <p class="lw-section-head">About this skill</p>
            <p class="lw-description">{{ $description }}</p>
        @endif

        <p class="lw-section-head">Things to help you</p>
        @if (count($resources) > 0)
            <ul class="lw-resources">
                @foreach ($resources as $resource)
                    @php
                        $label = is_array($resource) ? ($resource['title'] ?? $resource['label'] ?? null) : $resource;
                        $url   = is_array($resource) ? ($resource['url'] ?? null) : null;
                    @endphp
                    <li class="lw-resource">
                        @if ($url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $label }}</a>
                        @else
                            <span>{{ $label }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="lw-no-resources">No extra resources for this one yet — you're ready to practise! 🌱</p>
        @endif

        <a href="{{ route('practice.walk', $moduleId) }}" class="lw-start">Start practising →</a>
    </div>
</div>
</div>