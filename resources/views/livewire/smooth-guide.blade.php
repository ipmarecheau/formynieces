<div>
@php($c = $this->content())
@if($c)
<style>
    .sg-fab { position: fixed; right: 18px; bottom: 18px; z-index: 60; width: 60px; height: 60px; border-radius: 50%; border: 3px solid #67e8f9; background: #0c2440; padding: 4px; cursor: pointer; box-shadow: 0 6px 18px rgba(0,0,0,0.35); transition: transform 0.15s; }
    .sg-fab:hover { transform: scale(1.06); }
    .sg-fab img { width: 100%; height: 100%; object-fit: contain; }
    .sg-fab-badge { position: absolute; top: -4px; right: -4px; background: #f0abfc; color: #2a0a3a; font-family: 'Fredoka One', cursive; font-size: 12px; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .sg-overlay { position: fixed; inset: 0; z-index: 70; background: rgba(4,14,30,0.62); display: flex; align-items: center; justify-content: center; padding: 20px; animation: sgFade 0.25s ease both; }
    .sg-card { background: #0c2440; border: 2px solid rgba(103,232,249,0.5); border-radius: 24px; padding: 26px 24px 22px; width: 100%; max-width: 420px; text-align: center; animation: sgPop 0.3s ease both; }
    .sg-avatar { width: 104px; height: 104px; object-fit: contain; margin: -60px auto 6px; display: block; filter: drop-shadow(0 6px 12px rgba(0,0,0,0.4)); }
    .sg-title { font-family: 'Fredoka One', cursive; font-size: 21px; color: #67e8f9; margin: 0 0 14px; }
    .sg-lines { list-style: none; margin: 0 0 20px; padding: 0; display: flex; flex-direction: column; gap: 12px; }
    .sg-line { display: flex; gap: 10px; align-items: flex-start; font-size: 15.5px; line-height: 1.5; color: #e6f2fb; text-align: left; }
    .sg-num { flex: none; width: 24px; height: 24px; border-radius: 50%; background: linear-gradient(135deg,#0e7490,#f6b71e); color: #fff; font-family: 'Fredoka One', cursive; font-size: 13px; display: flex; align-items: center; justify-content: center; }
    .sg-dismiss { display: block; margin: 0 auto; background: linear-gradient(135deg,#0e7490,#f6b71e); border: none; border-radius: 999px; padding: 13px 34px; color: #fff; font-family: 'Fredoka One', cursive; font-size: 16px; cursor: pointer; }
    @keyframes sgFade { from { opacity: 0; } to { opacity: 1; } }
    @keyframes sgPop { from { opacity: 0; transform: translateY(14px) scale(0.96); } to { opacity: 1; transform: translateY(0) scale(1); } }
    @media (prefers-reduced-motion: reduce) { .sg-overlay, .sg-card { animation: none; } }
</style>

@if($open)
    <div class="sg-overlay" role="dialog" aria-modal="true" aria-label="{{ $c['title'] }}">
        <div class="sg-card">
            <img class="sg-avatar" src="{{ $this->avatarUrl($c['pose']) }}" alt="Smooth the turtle">
            <h2 class="sg-title">{{ $c['title'] }}</h2>
            <ul class="sg-lines">
                @foreach($c['lines'] as $i => $line)
                    <li class="sg-line"><span class="sg-num">{{ $i + 1 }}</span><span>{{ $line }}</span></li>
                @endforeach
            </ul>
            <button type="button" class="sg-dismiss" wire:click="dismiss">Got it! 🐢</button>
        </div>
    </div>
@else
    <button type="button" class="sg-fab" wire:click="reopen" aria-label="Ask Smooth how this works">
        <img src="{{ $this->avatarUrl($c['pose']) }}" alt="Smooth the turtle">
        <span class="sg-fab-badge">?</span>
    </button>
@endif
@endif
</div>
