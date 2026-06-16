{{-- resources/views/livewire/diagnostic-walk.blade.php --}}
<div>
@php
    $total = count(data_get($this->question, 'plan_total', []) ?: []) ?: 30;
    $current = data_get($this->question, 'item_number', 0);
@endphp

<style>
    .dw-wrap {
        min-height: 100vh; background: #0f0720;
        font-family: 'Nunito', sans-serif; color: #f3e8ff;
        display: flex; flex-direction: column; align-items: center;
        padding: 40px 20px;
    }
    .dw-dots { display: flex; gap: 6px; flex-wrap: wrap; justify-content: center;
        max-width: 420px; margin-bottom: 36px; }
    .dw-dot { width: 9px; height: 9px; border-radius: 50%;
        background: rgba(147,51,234,0.25); }
    .dw-dot.done { background: #c084fc; }
    .dw-dot.here { background: #f472b6; transform: scale(1.4); }
    .dw-card {
        background: #1a0d30; border: 1.5px solid rgba(147,51,234,0.35);
        border-radius: 24px; padding: 40px 32px;
        width: 100%; max-width: 600px;
    }
    .dw-prompt {
        font-family: 'Fredoka One', cursive; font-size: 22px;
        line-height: 1.4; margin-bottom: 28px; text-align: center;
        color: #f3e8ff;
    }
    .dw-options { display: flex; flex-direction: column; gap: 12px; }
    .dw-option {
        background: rgba(255,255,255,0.04);
        border: 1.5px solid rgba(147,51,234,0.3);
        border-radius: 14px; padding: 16px 20px;
        font-size: 16px; font-weight: 600; color: #f3e8ff;
        cursor: pointer; text-align: left; width: 100%;
        font-family: 'Nunito', sans-serif;
        transition: border-color 0.15s, background 0.15s, transform 0.1s;
    }
    .dw-option:hover { border-color: rgba(147,51,234,0.7);
        background: rgba(147,51,234,0.12); }
    .dw-option:active { transform: scale(0.99); }
    .dw-done {
        font-family: 'Fredoka One', cursive; font-size: 22px;
        text-align: center; color: #c084fc;
    }
</style>

<div class="dw-wrap">
    @if ($question === null)
        <div class="dw-card">
            <p class="dw-done">You've explored every island! 🗺️✨</p>
        </div>
    @else
        <div class="dw-dots">
            @for ($i = 1; $i <= 30; $i++)
                <span class="dw-dot
                    {{ $i < $current ? 'done' : '' }}
                    {{ $i === $current ? 'here' : '' }}"></span>
            @endfor
        </div>

        <div class="dw-card">
            <p class="dw-prompt">{{ $prompt }}</p>
            <div class="dw-options">
                @foreach ($options as $index => $optionText)
                    <button
                        type="button"
                        class="dw-option"
                        wire:click="choose({{ $index }})"
                        wire:loading.attr="disabled"
                    >{{ $optionText }}</button>
                @endforeach
            </div>
        </div>
    @endif
</div>
</div>