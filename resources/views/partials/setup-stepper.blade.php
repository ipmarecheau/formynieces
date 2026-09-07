{{-- GO-10 — where the guardian is in the account-setup journey.
     Pass $current (1-based) for the active step. --}}
@php($steps = ['Create account', 'Set up your child', 'Start the diagnostic'])
<div class="setup-steps" aria-label="Setup progress">
    <p class="setup-steps__count">Step {{ $current }} of {{ count($steps) }}</p>
    <ol class="setup-steps__list">
        @foreach ($steps as $i => $label)
            @php($n = $i + 1)
            <li class="setup-steps__item {{ $n < $current ? 'is-done' : ($n === $current ? 'is-current' : '') }}">
                <span class="setup-steps__dot">{{ $n < $current ? '✓' : $n }}</span>
                <span class="setup-steps__label">{{ $label }}</span>
            </li>
        @endforeach
    </ol>
</div>
<style>
    .setup-steps { margin: 0 0 24px; }
    .setup-steps__count {
        text-align: center; font-size: 12px; font-weight: 700;
        letter-spacing: 0.06em; text-transform: uppercase; color: #0d9488; margin-bottom: 10px;
    }
    .setup-steps__list {
        list-style: none; padding: 0; margin: 0;
        display: flex; align-items: flex-start; justify-content: space-between; gap: 6px;
    }
    .setup-steps__item {
        flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; text-align: center;
    }
    .setup-steps__dot {
        width: 28px; height: 28px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700; font-family: 'Nunito', sans-serif;
        background: #f0f4f2; border: 1.5px solid rgba(13,125,140,0.35); color: #475569;
    }
    .setup-steps__label { font-size: 11px; line-height: 1.3; color: #475569; }
    .setup-steps__item.is-current .setup-steps__dot {
        background: #0d9488; border-color: transparent; color: #fff;
        box-shadow: 0 0 0 4px rgba(13,125,140,0.14);
    }
    .setup-steps__item.is-current .setup-steps__label { color: #0f172a; font-weight: 700; }
    .setup-steps__item.is-done .setup-steps__dot { border-color: rgba(21,128,61,0.5); color: #15803d; }
</style>
