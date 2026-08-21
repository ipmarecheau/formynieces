<div x-data="{
        open: false, glow: false, shake: false,
        fabX: 0, fabY: 0,
        // Drag the bubble (minimized chat only). Listeners live on window so the drag never
        // stalls on the first try or when the pointer leaves the small bubble. A tap (no real
        // movement) still opens the chat.
        fabDown(e) {
            const s = { x: e.clientX, y: e.clientY, ox: this.fabX, oy: this.fabY, moved: false };
            const move = (ev) => { const dx = ev.clientX - s.x, dy = ev.clientY - s.y; if (Math.abs(dx) + Math.abs(dy) > 4) s.moved = true; this.fabX = s.ox + dx; this.fabY = s.oy + dy; };
            const up = () => { window.removeEventListener('pointermove', move); window.removeEventListener('pointerup', up); if (!s.moved) this.open = true; };
            window.addEventListener('pointermove', move); window.addEventListener('pointerup', up);
        },
    }"
    @smooth-spoke.window="glow = true; shake = true; setTimeout(() => shake = false, 600); $nextTick(() => { if ($refs.log) $refs.log.scrollTop = $refs.log.scrollHeight; if ($refs.ccInput) $refs.ccInput.value = '' })">
    @php $locked = in_array($reteachMode, ['remediation', 'final'], true); @endphp
    <style>
        [x-cloak] { display: none !important; }
        .cc-fab { position: fixed; z-index: 80; right: 20px; bottom: 20px; width: 66px; height: 66px; border-radius: 50%; border: 3px solid #67e8f9; background: #0c2440; padding: 4px; cursor: grab; touch-action: none; box-shadow: 0 8px 22px rgba(0,0,0,0.4); }
        .cc-fab:active { cursor: grabbing; }
        .cc-fab:hover { transform: scale(1.06); }
        .cc-fab img { width: 100%; height: 100%; object-fit: contain; border-radius: 50%; }
        .cc-fab-grip { position: absolute; top: -6px; right: -6px; width: 24px; height: 24px; border-radius: 50%; background: #67e8f9; color: #0c2440; font-size: 12px; line-height: 1; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.4); }
        .cc-fab-badge { position: absolute; top: -3px; left: -3px; background: #f0abfc; border-radius: 50%; min-width: 22px; height: 22px; font-size: 12px; display: flex; align-items: center; justify-content: center; }
        .cc-backdrop { position: fixed; inset: 0; z-index: 79; background: rgba(4,14,30,0.62); }
        .cc-widget { position: fixed; z-index: 80; right: 20px; bottom: 20px; width: 400px; max-width: calc(100vw - 40px); height: 70vh; max-height: 640px; }
        @media (max-width: 560px) { .cc-widget { right: 0; left: 0; bottom: 0; top: 0; width: 100%; height: 100%; max-height: none; } }
        .cc-min { margin-left: auto; background: rgba(255,255,255,0.08); border: none; color: #e6f2fb; width: 32px; height: 32px; border-radius: 50%; font-size: 22px; line-height: 1; cursor: pointer; }
        .cc-min:hover { background: rgba(255,255,255,0.16); }
        .cc-panel { display: flex; flex-direction: column; height: 100%; background: #0a1f38; border: 2px solid rgba(34,211,238,0.3); border-radius: 20px; overflow: hidden; transition: border-color 0.2s; box-shadow: 0 12px 40px rgba(0,0,0,0.45); }
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
        .cc-warmup { align-self: stretch; background: rgba(246,183,30,0.10); border: 1.5px solid rgba(246,183,30,0.4); border-radius: 16px; padding: 14px 16px; animation: ccFade 0.32s ease both; }
        .cc-warmup-tag { font-family: 'Fredoka One', cursive; font-size: 12px; text-transform: uppercase; letter-spacing: 0.06em; color: #f6b71e; margin: 0 0 8px; }
        .cc-warmup-q { font-size: 16.5px; font-weight: 700; color: #eaf9ff; line-height: 1.5; margin: 0 0 12px; }
        .cc-warmup-opts { display: flex; flex-direction: column; gap: 8px; }
        .cc-warmup-opt { text-align: left; background: rgba(255,255,255,0.06); border: 2px solid rgba(246,183,30,0.35); border-radius: 12px; padding: 11px 14px; color: #eaf9ff; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.15s, transform 0.1s; }
        .cc-warmup-opt:hover { background: rgba(246,183,30,0.16); transform: translateY(-1px); }
        .cc-warmup-opt:disabled { opacity: 0.6; cursor: default; }
        .cc-worked { font-size: 15.5px; line-height: 1.6; color: #eaf9ff; background: rgba(255,255,255,0.05); border-left: 3px solid #67e8f9; border-radius: 8px; padding: 10px 12px; margin: 0 0 12px; }
    </style>

    {{-- Collapsed bubble + its dim-on-open backdrop — only when Smooth isn't mid-required-step. --}}
    @if (! $locked)
        <button type="button" class="cc-fab" x-show="!open" @pointerdown="fabDown" :style="`transform: translate(${fabX}px, ${fabY}px)`" aria-label="Chat with Smooth — tap to open, drag to move">
            <img src="{{ asset('images/voyage/companion/smooth-chart.webp') }}" alt="Smooth">
            <span class="cc-fab-grip" aria-hidden="true">⠿</span>
            @if (count($messages) > 0)<span class="cc-fab-badge">💬</span>@endif
        </button>
        <div class="cc-backdrop" x-show="open" x-cloak style="display:none"></div>
    @else
        {{-- Required step: dim the lesson and keep the panel locked open. --}}
        <div class="cc-backdrop"></div>
    @endif

    <div class="cc-widget" @if (! $locked) x-show="open" x-cloak style="display:none" @endif>
        <div class="cc-panel" :class="{ 'is-glowing': glow, 'is-shaking': shake }" @focusin="glow = false">
            <div class="cc-head">
                <img src="{{ asset('images/voyage/companion/smooth-chart.webp') }}" alt="Smooth">
                <span><b>Ask Smooth</b><small>your lesson buddy 🐢</small></span>
                @if (! $locked)
                    <button type="button" class="cc-min" @click="open = false" aria-label="Minimize chat">–</button>
                @endif
            </div>

            <div class="cc-log" wire:key="cc-log" x-ref="log">
                @forelse ($messages as $i => $message)
                    <div class="cc-msg {{ $message['role'] }}" wire:key="cc-msg-{{ $i }}">{{ $message['content'] }}</div>
                @empty
                    <div class="cc-empty">
                        @if ($reteach)
                            <p>I'm right here with you 🐢<br>Work through the lesson — I'll pop in if something's tricky!</p>
                        @else
                            <p>Hi! I'm Smooth 🐢<br>Tap a button, or type to me!</p>
                            <div class="cc-chips">
                                @foreach (\App\Livewire\ClarifyChat::STARTERS as $starter)
                                    <button type="button" class="cc-chip" wire:click="ask(@js($starter))">{{ $starter }}</button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforelse

                @if ($reteachMode === 'remediation')
                    <div class="cc-warmup" wire:key="rem-{{ $remStep }}">
                        @if ($remStep === 'check')
                            <p class="cc-warmup-tag">Your turn ✏️</p>
                            <p class="cc-warmup-q">Type {{ $remItem['prompt'] ?? 'your answer' }}</p>
                        @else
                            <p class="cc-warmup-tag">Say it back ✏️</p>
                            <p class="cc-warmup-q">In your own words — what's the rule?</p>
                        @endif
                        <p class="cc-worked">Type your answer below and tap ➤</p>
                    </div>
                @elseif ($reteachMode === 'final' && isset($finalItems[$finalAt]))
                    <div class="cc-warmup" wire:key="final-{{ $finalAt }}">
                        <p class="cc-warmup-tag">Review {{ $finalAt + 1 }} of {{ count($finalItems) }} ✏️</p>
                        <p class="cc-warmup-q">Type {{ $finalItems[$finalAt]['prompt'] }}</p>
                        <p class="cc-worked">Type your answer below and tap ➤</p>
                    </div>
                @endif

                <div wire:loading.flex wire:target="send" class="cc-thinking" style="display: none;">Smooth is thinking… 🐢</div>
            </div>

            <form class="cc-form" wire:submit="send">
                <input class="cc-input" type="text" wire:model="draft" x-ref="ccInput" placeholder="Type to Smooth…" maxlength="300" autocomplete="off">
                <button class="cc-send" type="submit" wire:loading.attr="disabled" wire:target="send" aria-label="Send">➤</button>
            </form>
        </div>
    </div>
</div>
