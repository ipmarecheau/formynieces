@extends('layouts.public')

@section('title', 'Book a call — SmoothSeas')

@section('styles')
<style>
    .book-hero { text-align: center; padding: 76px 0 40px; }
    .book-hero h1 {
        font-family: 'Fredoka One', cursive; font-size: clamp(30px, 5.4vw, 46px);
        background: linear-gradient(135deg, #ecfeff 0%, var(--aqua) 45%, #fcd34d 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        margin-bottom: 16px;
    }
    .book-hero p { font-size: 17.5px; line-height: 1.7; color: var(--muted); max-width: 600px; margin: 0 auto; }
    .window-note {
        display: inline-flex; align-items: center; gap: 8px; margin-top: 22px;
        background: rgba(34,211,238,.14); border: 1.5px solid rgba(34,211,238,.4);
        border-radius: 999px; padding: 8px 18px; font-size: 13.5px; font-weight: 700; color: var(--aqua);
    }

    .book-grid { display: grid; grid-template-columns: 1fr; gap: 22px; }

    .day-card { background: var(--card); border: 1.5px solid var(--border); border-radius: 18px; padding: 18px 20px; margin-bottom: 14px; }
    .day-head { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 12px; }
    .day-name { font-family: 'Fredoka One', cursive; font-size: 15.5px; color: var(--aqua); }
    .day-count { font-size: 12px; font-weight: 700; color: var(--dim); }
    .slots { display: flex; flex-wrap: wrap; gap: 8px; }
    .slot-chip { position: relative; }
    .slot-chip input { position: absolute; opacity: 0; inset: 0; cursor: pointer; }
    .slot-chip label {
        display: inline-block; padding: 8px 14px; border-radius: 999px; cursor: pointer;
        background: var(--card2); border: 1.5px solid var(--border); color: var(--muted);
        font-size: 13.5px; font-weight: 700; transition: all .15s;
    }
    .slot-chip input:checked + label {
        background: linear-gradient(135deg, var(--teal-deep), var(--gold)); color: #fff; border-color: transparent;
        box-shadow: 0 0 14px rgba(34,211,238,.4);
    }
    .slot-chip input:focus-visible + label { outline: 2px solid var(--aqua); outline-offset: 2px; }
    .slot-hint { font-size: 12.5px; color: var(--dim); font-weight: 700; margin: 4px 0 16px; }

    .details-card { background: var(--card); border: 1.5px solid var(--border); border-radius: 22px; padding: 30px 28px; }
    .field { margin-bottom: 18px; }
    .field label { display: block; font-size: 13.5px; font-weight: 800; color: var(--muted); margin-bottom: 7px; }
    .field input, .field select, .field textarea {
        width: 100%; background: var(--card2); border: 1.5px solid var(--border); border-radius: 12px;
        padding: 12px 14px; color: var(--text); font-family: 'Nunito', sans-serif; font-size: 15px;
        outline: none; transition: border-color .2s;
    }
    .field input:focus, .field select:focus, .field textarea:focus { border-color: rgba(34,211,238,.6); }
    .field textarea { min-height: 90px; resize: vertical; }
    .field .err { color: #fda4af; font-size: 12.5px; font-weight: 700; margin-top: 6px; }

    .booked-banner {
        background: linear-gradient(135deg, rgba(13,148,136,.25), rgba(34,211,238,.18));
        border: 1.5px solid rgba(94,234,212,.55); border-radius: 22px; padding: 34px 30px; text-align: center;
    }
    .booked-banner .b-icon { font-size: 38px; display: block; margin-bottom: 12px; }
    .booked-banner h2 { font-family: 'Fredoka One', cursive; font-size: 24px; margin-bottom: 12px; color: #5eead4; }
    .booked-banner p { font-size: 15.5px; line-height: 1.7; color: var(--muted); max-width: 460px; margin: 0 auto 8px; }
    .booked-when { font-family: 'Fredoka One', cursive; font-size: 19px; color: var(--gold-light); margin: 10px 0 18px; display: block; }
</style>
@endsection

@section('content')
    <section class="book-hero">
        <div class="container">
            <h1 data-reveal>Book your onboarding call.</h1>
            <p data-reveal style="--rd:.08s">
                Fifteen minutes, free, no pressure. We will look at where your child is, answer your
                questions, and show you exactly how their voyage would look — Math, ELA and Writing.
            </p>
            <div class="window-note" data-reveal style="--rd:.14s">🕒 Weekdays 5pm–8pm · Saturdays 8am–5pm · Trinidad &amp; Tobago time</div>
        </div>
    </section>

    <section>
        <div class="container">
            @if ($booked)
                <div class="booked-banner" data-reveal>
                    <span class="b-icon">⚓</span>
                    <h2>You are on the calendar!</h2>
                    <p>{{ $booked->parent_name }}, your 15-minute onboarding call is booked for:</p>
                    <span class="booked-when">{{ app(\App\Services\Onboarding\CallSlotGenerator::class)->label($booked->call_date->format('Y-m-d'), $booked->call_time->format('H:i')) }}</span>
                    <p>We will confirm by email at <strong>{{ $booked->email }}</strong>. If anything changes, just <a href="{{ route('contact') }}" style="color: var(--aqua);">send us a message</a>.</p>
                </div>
            @else
                <div class="book-grid">
                    <form method="POST" action="{{ route('book.store') }}" data-reveal>
                        @csrf
                        <p class="slot-hint">1 — Pick a day and time (15 minutes):</p>

                        @foreach ($days as $day)
                            <div class="day-card">
                                <div class="day-head">
                                    <span class="day-name">{{ $day['label'] }}</span>
                                    <span class="day-count">{{ count($day['slots']) }} open</span>
                                </div>
                                <div class="slots">
                                    @foreach ($day['slots'] as $time)
                                        <span class="slot-chip">
                                            <input type="radio" id="slot-{{ $day['date'] }}-{{ str_replace(':', '', $time) }}" name="slot" value="{{ $day['date'] }}|{{ $time }}" @checked(old('slot') === $day['date'].'|'.$time) required>
                                            <label for="slot-{{ $day['date'] }}-{{ str_replace(':', '', $time) }}">{{ \Carbon\Carbon::parse($time)->format('g:ia') }}</label>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        @error('slot')<div class="field"><div class="err">{{ $message }}</div></div>@enderror

                        <p class="slot-hint" style="margin-top: 20px;">2 — Tell us who we are calling:</p>
                        <div class="details-card">
                            <div class="field">
                                <label for="parent_name">Your name</label>
                                <input id="parent_name" name="parent_name" type="text" value="{{ old('parent_name') }}" required>
                                @error('parent_name')<div class="err">{{ $message }}</div>@enderror
                            </div>
                            <div class="field">
                                <label for="email">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                                @error('email')<div class="err">{{ $message }}</div>@enderror
                            </div>
                            <div class="field">
                                <label for="phone">Phone (optional)</label>
                                <input id="phone" name="phone" type="text" value="{{ old('phone') }}">
                                @error('phone')<div class="err">{{ $message }}</div>@enderror
                            </div>
                            <div class="field">
                                <label for="child_standard">Your child is in…</label>
                                <select id="child_standard" name="child_standard">
                                    <option value="">—</option>
                                    @foreach (['Standard 3', 'Standard 4', 'Standard 5'] as $std)
                                        <option value="{{ $std }}" @selected(old('child_standard') === $std)>{{ $std }}</option>
                                    @endforeach
                                </select>
                                @error('child_standard')<div class="err">{{ $message }}</div>@enderror
                            </div>
                            <div class="field">
                                <label for="notes">Anything we should know? (optional)</label>
                                <textarea id="notes" name="notes">{{ old('notes') }}</textarea>
                                @error('notes')<div class="err">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn-primary">Book my call</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </section>
@endsection
