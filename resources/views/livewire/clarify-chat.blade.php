<div class="cc-panel">
    <style>
        .cc-panel { display: flex; flex-direction: column; height: 100%; min-height: 420px; background: #0a1f38; border: 1.5px solid rgba(34,211,238,0.3); border-radius: 20px; overflow: hidden; }
        .cc-head { display: flex; align-items: center; gap: 10px; padding: 14px 16px; background: rgba(34,211,238,0.08); border-bottom: 1.5px solid rgba(34,211,238,0.2); }
        .cc-head img { width: 34px; height: 34px; object-fit: contain; }
        .cc-head b { font-family: 'Fredoka One', cursive; font-size: 15px; color: #67e8f9; }
        .cc-head small { display: block; font-size: 11.5px; color: rgba(196,181,253,0.7); }
        .cc-log { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 10px; }
        .cc-empty { margin: auto; text-align: center; color: rgba(196,181,253,0.7); font-size: 14px; line-height: 1.5; padding: 0 10px; }
        .cc-msg { max-width: 86%; padding: 11px 14px; border-radius: 14px; font-size: 15.5px; line-height: 1.6; }
        .cc-msg.user { align-self: flex-end; background: linear-gradient(135deg,#0e7490,#f6b71e); color: #fff; border-bottom-right-radius: 4px; }
        .cc-msg.assistant { align-self: flex-start; background: rgba(255,255,255,0.06); color: #e6f2fb; border: 1px solid rgba(34,211,238,0.25); border-bottom-left-radius: 4px; }
        .cc-form { display: flex; gap: 8px; padding: 12px; border-top: 1.5px solid rgba(34,211,238,0.2); }
        .cc-input { flex: 1; background: rgba(255,255,255,0.06); border: 1.5px solid rgba(34,211,238,0.3); border-radius: 999px; padding: 10px 16px; color: #e6f2fb; font-size: 14px; }
        .cc-input:focus { outline: none; border-color: #67e8f9; }
        .cc-send { flex: 0 0 auto; background: linear-gradient(135deg,#0e7490,#f6b71e); border: none; border-radius: 999px; width: 42px; height: 42px; color: #fff; font-size: 18px; cursor: pointer; }
        .cc-send:disabled { opacity: 0.5; cursor: default; }
        .cc-thinking { align-self: flex-start; color: rgba(196,181,253,0.8); font-size: 13px; font-style: italic; }
    </style>

    <div class="cc-head">
        <img src="{{ asset('images/voyage/companion/smooth-chart.webp') }}" alt="Smooth">
        <span><b>Ask Smooth</b><small>about this lesson</small></span>
    </div>

    <div class="cc-log" wire:key="cc-log">
        @forelse ($messages as $i => $message)
            <div class="cc-msg {{ $message['role'] }}" wire:key="cc-msg-{{ $i }}">{{ $message['content'] }}</div>
        @empty
            <p class="cc-empty">Stuck on something? Ask me about this lesson and I'll help you figure it out — one step at a time. 🐢</p>
        @endforelse

        <div wire:loading wire:target="send" class="cc-thinking">Smooth is thinking…</div>
    </div>

    <form class="cc-form" wire:submit="send">
        <input class="cc-input" type="text" wire:model="draft" placeholder="Ask about this lesson…" maxlength="300" autocomplete="off">
        <button class="cc-send" type="submit" wire:loading.attr="disabled" wire:target="send" aria-label="Send">➤</button>
    </form>
</div>
