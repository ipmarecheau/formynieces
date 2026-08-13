<div class="cc-panel" x-data="{ glow: false, shake: false }" :class="{ 'is-glowing': glow, 'is-shaking': shake }"
    @smooth-spoke.window="glow = true; shake = true; setTimeout(() => shake = false, 600); $nextTick(() => { if ($refs.log) $refs.log.scrollTop = $refs.log.scrollHeight })"
    @focusin="glow = false">
    <style>
        .cc-panel { display: flex; flex-direction: column; height: 100%; min-height: 420px; background: #0a1f38; border: 2px solid rgba(34,211,238,0.3); border-radius: 20px; overflow: hidden; transition: border-color 0.2s; }
        .cc-panel.is-glowing { border-color: #f87171; animation: ccRedGlow 1.3s ease-in-out infinite; }
        .cc-panel.is-shaking { animation: ccShake 0.6s ease-in-out; }
        .cc-panel.is-shaking.is-glowing { animation: ccShake 0.6s ease-in-out, ccRedGlow 1.3s ease-in-out infinite; }
        @keyframes ccRedGlow { 0%,100% { box-shadow: 0 0 14px 2px rgba(248,113,113,0.5); } 50% { box-shadow: 0 0 34px 7px rgba(248,113,113,0.9); } }
        @keyframes ccShake { 0%,100% { transform: translateX(0); } 12% { transform: translateX(-8px); } 26% { transform: translateX(7px); } 40% { transform: translateX(-6px); } 55% { transform: translateX(5px); } 70% { transform: translateX(-3px); } 85% { transform: translateX(2px); } }
        @media (prefers-reduced-motion: reduce) { .cc-panel.is-shaking, .cc-panel.is-shaking.is-glowing { animation: ccRedGlow 1.3s ease-in-out infinite; } }
        .cc-head { display: flex; align-items: center; gap: 10px; padding: 14px 16px; background: rgba(34,211,238,0.08); border-bottom: 1.5px solid rgba(34,211,238,0.2); }
        .cc-head img { width: 40px; height: 40px; object-fit: contain; }
        .cc-head b { font-family: 'Fredoka One', cursive; font-size: 17px; color: #67e8f9; }
        .cc-head small { display: block; font-size: 12px; color: rgba(196,181,253,0.7); }
        .cc-log { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px; scroll-behavior: smooth; }
        .cc-empty { margin: auto; text-align: center; color: rgba(230,242,251,0.9); font-size: 17px; line-height: 1.6; padding: 0 6px; }
        .cc-chips { display: flex; flex-direction: column; gap: 10px; margin-top: 18px; }
        .cc-chip { background: rgba(34,211,238,0.12); border: 2px solid rgba(34,211,238,0.4); border-radius: 999px; padding: 12px 16px; color: #67e8f9; font-size: 16px; font-weight: 700; cursor: pointer; transition: background 0.15s, transform 0.1s; }
        .cc-chip:hover { background: rgba(34,211,238,0.22); transform: translateY(-1px); }
        .cc-msg { max-width: 88%; padding: 13px 16px; border-radius: 16px; font-size: 17.5px; line-height: 1.6; animation: ccFade 0.32s ease both; }
        @keyframes ccFade { from { opacity: 0; transform: translateY(7px); } to { opacity: 1; transform: none; } }
        .cc-msg.user { align-self: flex-end; background: linear-gradient(135deg,#0e7490,#f6b71e); color: #fff; border-bottom-right-radius: 5px; }
        .cc-msg.assistant { align-self: flex-start; background: rgba(103,232,249,0.12); color: #eaf9ff; border: 1.5px solid rgba(34,211,238,0.4); border-bottom-left-radius: 5px; font-weight: 500; }
        .cc-form { display: flex; gap: 8px; padding: 12px; border-top: 1.5px solid rgba(34,211,238,0.2); }
        .cc-input { flex: 1; background: rgba(255,255,255,0.06); border: 2px solid rgba(34,211,238,0.3); border-radius: 999px; padding: 12px 18px; color: #e6f2fb; font-size: 16px; transition: border-color 0.2s, box-shadow 0.2s; }
        .cc-input:focus { outline: none; border-color: #67e8f9; }
        .cc-panel.is-glowing .cc-input { border-color: #f87171; animation: ccInputRed 1.3s ease-in-out infinite; }
        @keyframes ccInputRed { 0%,100% { box-shadow: 0 0 0 rgba(248,113,113,0); } 50% { box-shadow: 0 0 12px rgba(248,113,113,0.7); } }
        .cc-send { flex: 0 0 auto; background: linear-gradient(135deg,#0e7490,#f6b71e); border: none; border-radius: 999px; width: 46px; height: 46px; color: #fff; font-size: 20px; cursor: pointer; }
        .cc-send:disabled { opacity: 0.5; cursor: default; }
        .cc-thinking { align-self: flex-start; color: #7dd3fc; font-size: 15px; font-weight: 600; }
    </style>

    <div class="cc-head">
        <img src="{{ asset('images/voyage/companion/smooth-chart.webp') }}" alt="Smooth">
        <span><b>Ask Smooth</b><small>your lesson buddy 🐢</small></span>
    </div>

    <div class="cc-log" wire:key="cc-log" x-ref="log">
        @forelse ($messages as $i => $message)
            <div class="cc-msg {{ $message['role'] }}" wire:key="cc-msg-{{ $i }}">{{ $message['content'] }}</div>
        @empty
            <div class="cc-empty">
                <p>Hi! I'm Smooth 🐢<br>Tap a button, or type to me!</p>
                <div class="cc-chips">
                    @foreach (\App\Livewire\ClarifyChat::STARTERS as $starter)
                        <button type="button" class="cc-chip" wire:click="ask(@js($starter))">{{ $starter }}</button>
                    @endforeach
                </div>
            </div>
        @endforelse

        <div wire:loading.flex wire:target="send,reinforce" class="cc-thinking" style="display: none;">Smooth is thinking… 🐢</div>
    </div>

    <form class="cc-form" wire:submit="send">
        <input class="cc-input" type="text" wire:model="draft" placeholder="Type to Smooth…" maxlength="300" autocomplete="off">
        <button class="cc-send" type="submit" wire:loading.attr="disabled" wire:target="send" aria-label="Send">➤</button>
    </form>
</div>
