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

    .sg-fab-locked { opacity: 0.5; cursor: default; filter: grayscale(0.6); }
    .sg-chat { position: fixed; right: 18px; bottom: 18px; z-index: 65; width: min(360px, calc(100vw - 36px)); background: #0c2440; border: 2px solid rgba(103,232,249,0.5); border-radius: 20px; box-shadow: 0 20px 50px -18px rgba(0,0,0,0.7); overflow: hidden; animation: sgPop 0.25s ease both; display: flex; flex-direction: column; max-height: min(560px, calc(100vh - 36px)); }
    .sg-chat-head { display: flex; align-items: center; gap: 10px; padding: 12px 14px; background: linear-gradient(135deg,#0e7490,#0c2440); }
    .sg-chat-head img { width: 40px; height: 40px; object-fit: contain; }
    .sg-chat-head b { font-family: 'Fredoka One', cursive; color: #67e8f9; font-size: 16px; }
    .sg-chat-head .sg-x { margin-left: auto; background: none; border: none; color: #93b2cc; font-size: 22px; cursor: pointer; line-height: 1; }
    .sg-chat-body { padding: 14px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; flex: 1; }
    .sg-msg { max-width: 82%; padding: 9px 12px; border-radius: 14px; font-size: 14.5px; line-height: 1.5; }
    .sg-msg-user { align-self: flex-end; background: linear-gradient(135deg,#0e7490,#f6b71e); color: #fff; border-bottom-right-radius: 4px; }
    .sg-msg-smooth { align-self: flex-start; background: #12335a; color: #e6f2fb; border-bottom-left-radius: 4px; }
    .sg-chips { display: flex; flex-wrap: wrap; gap: 7px; }
    .sg-chip { background: rgba(103,232,249,0.12); border: 1px solid rgba(103,232,249,0.4); color: #a5e9f5; border-radius: 999px; padding: 7px 12px; font-size: 12.5px; font-weight: 700; cursor: pointer; }
    .sg-chat-foot { display: flex; gap: 8px; padding: 12px 14px; border-top: 1px solid rgba(103,232,249,0.2); }
    .sg-chat-foot input { flex: 1; background: #06182e; border: 1.5px solid rgba(103,232,249,0.3); border-radius: 12px; padding: 11px 12px; color: #e6f2fb; font-family: 'Nunito', sans-serif; font-size: 15px; }
    .sg-chat-foot input:focus { outline: none; border-color: #67e8f9; }
    .sg-chat-foot button { background: linear-gradient(135deg,#0e7490,#f6b71e); border: none; border-radius: 12px; color: #fff; font-size: 18px; padding: 0 16px; cursor: pointer; }
    .sg-locked-note { padding: 16px 14px; text-align: center; color: #93b2cc; font-size: 14px; line-height: 1.5; }
    .sg-howlink { display: block; margin: 2px 14px 12px; background: none; border: none; color: #67e8f9; font-size: 12.5px; font-weight: 700; cursor: pointer; text-align: left; }
    @media (prefers-reduced-motion: reduce) { .sg-overlay, .sg-card, .sg-chat { animation: none; } }
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
@elseif($chatOpen)
    <div class="sg-chat" role="dialog" aria-label="Ask Smooth">
        <div class="sg-chat-head">
            <img src="{{ $this->avatarUrl('wave') }}" alt="Smooth the turtle">
            <b>Ask Smooth 🐢</b>
            <button type="button" class="sg-x" wire:click="closeChat" aria-label="Close">×</button>
        </div>
        <button type="button" class="sg-howlink" wire:click="reopen">💡 How does this screen work?</button>
        <div class="sg-chat-body">
            @forelse($messages as $m)
                <div class="sg-msg {{ $m['role'] === 'user' ? 'sg-msg-user' : 'sg-msg-smooth' }}">{{ $m['content'] }}</div>
            @empty
                <div class="sg-msg sg-msg-smooth">Hi! I'm Smooth, your guide. Ask me anything about your lessons, your Voyage, or how things work. 🌊</div>
                <div class="sg-chips">
                    <button type="button" class="sg-chip" wire:click="$set('draft', 'What should I do next?')">What should I do next?</button>
                    <button type="button" class="sg-chip" wire:click="$set('draft', 'How does my Voyage work?')">How does my Voyage work?</button>
                </div>
            @endforelse
            <div wire:loading wire:target="sendToSmooth" class="sg-msg sg-msg-smooth">Smooth is thinking… 🐢</div>
        </div>
        <form class="sg-chat-foot" wire:submit="sendToSmooth">
            <input type="text" wire:model="draft" placeholder="Ask Smooth…" autocomplete="off" aria-label="Your message to Smooth">
            <button type="submit" aria-label="Send">➤</button>
        </form>
    </div>
@elseif($locked)
    <button type="button" class="sg-fab sg-fab-locked" disabled aria-label="Smooth is waiting quietly while you focus">
        <img src="{{ $this->avatarUrl($c['pose']) }}" alt="Smooth the turtle, resting">
    </button>
@else
    <button type="button" class="sg-fab" wire:click="{{ $chat ? 'openChat' : 'reopen' }}" aria-label="Ask Smooth">
        <img src="{{ $this->avatarUrl($c['pose']) }}" alt="Smooth the turtle">
        <span class="sg-fab-badge">{{ $chat ? '💬' : '?' }}</span>
    </button>
@endif
@endif
</div>
