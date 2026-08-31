@extends('layouts.public')

@section('title', 'Contact SmoothSeas — SEA prep support for parents')
@section('description', 'Get in touch with the SmoothSeas team about SEA exam preparation, your account, or a question about the voyage. A real person will reply.')

@section('styles')
<style>
    .contact-hero { text-align: center; padding: 76px 0 40px; }
    .contact-hero h1 {
        font-family: 'Fredoka One', cursive; font-size: clamp(30px, 5.4vw, 46px);
        background: linear-gradient(135deg, #ecfeff 0%, var(--aqua) 45%, #fcd34d 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        margin-bottom: 16px;
    }
    .contact-hero p { font-size: 17.5px; line-height: 1.7; color: var(--muted); max-width: 560px; margin: 0 auto; }

    .contact-grid { display: grid; grid-template-columns: 1.15fr .85fr; gap: 22px; align-items: start; }
    .contact-card { background: var(--card); border: 1.5px solid var(--border); border-radius: 22px; padding: 32px 30px; }
    .field { margin-bottom: 18px; }
    .field label { display: block; font-size: 13.5px; font-weight: 800; color: var(--muted); margin-bottom: 7px; }
    .field input, .field select, .field textarea {
        width: 100%; background: var(--card2); border: 1.5px solid var(--border); border-radius: 12px;
        padding: 12px 14px; color: var(--text); font-family: 'Nunito', sans-serif; font-size: 15px;
        outline: none; transition: border-color .2s;
    }
    .field input:focus, .field select:focus, .field textarea:focus { border-color: rgba(34,211,238,.6); }
    .field textarea { min-height: 130px; resize: vertical; }
    .field .err { color: #fda4af; font-size: 12.5px; font-weight: 700; margin-top: 6px; }
    .contact-side { display: flex; flex-direction: column; gap: 18px; }
    .side-card { background: var(--card2); border: 1.5px solid var(--border); border-radius: 20px; padding: 24px 22px; }
    .side-card .s-icon { font-size: 24px; display: block; margin-bottom: 10px; }
    .side-card h3 { font-family: 'Fredoka One', cursive; font-size: 15.5px; color: var(--aqua); margin-bottom: 6px; }
    .side-card p { font-size: 13.8px; line-height: 1.65; color: var(--dim); }
    .side-card a { color: var(--aqua); }
    .sent-banner {
        background: rgba(13,148,136,.18); border: 1.5px solid rgba(94,234,212,.5); color: #5eead4;
        border-radius: 14px; padding: 14px 18px; font-weight: 700; font-size: 14.5px; margin-bottom: 20px;
    }
    @media (max-width: 780px) { .contact-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
    <section class="contact-hero">
        <div class="container">
            <h1 data-reveal>Talk to a human.</h1>
            <p data-reveal style="--rd:.08s">A question about the programme, billing, or whether SmoothSeas fits your child? Send it — we answer within one business day.</p>
        </div>
    </section>

    <section>
        <div class="container">
            <div class="contact-grid">
                <div class="contact-card" data-reveal>
                    @if (session('contact_sent'))
                        <div class="sent-banner">✅ Message received — thank you. We will reply within one business day.</div>
                    @endif

                    <form method="POST" action="{{ route('contact.send') }}">
                        @csrf
                        <div class="field">
                            <label for="name">Your name</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                            @error('name')<div class="err">{{ $message }}</div>@enderror
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                            @error('email')<div class="err">{{ $message }}</div>@enderror
                        </div>
                        <div class="field">
                            <label for="topic">Topic</label>
                            <select id="topic" name="topic" required>
                                <option value="general" @selected(old('topic', 'general') === 'general')>General question</option>
                                <option value="onboarding" @selected(old('topic') === 'onboarding')>Onboarding my child</option>
                                <option value="billing" @selected(old('topic') === 'billing')>Billing &amp; refunds</option>
                                <option value="technical" @selected(old('topic') === 'technical')>Technical trouble</option>
                                <option value="feedback" @selected(old('topic') === 'feedback')>Feedback</option>
                            </select>
                            @error('topic')<div class="err">{{ $message }}</div>@enderror
                        </div>
                        <div class="field">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" required>{{ old('message') }}</textarea>
                            @error('message')<div class="err">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn-primary">Send message</button>
                    </form>
                </div>

                <div class="contact-side">
                    <div class="side-card" data-reveal style="--rd:.1s">
                        <span class="s-icon">📞</span>
                        <h3>Prefer to just talk?</h3>
                        <p>Book a free <a href="{{ route('book.call') }}">15-minute onboarding call</a> — weekdays 5–8pm, Saturdays 8–5.</p>
                    </div>
                    <div class="side-card" data-reveal style="--rd:.2s">
                        <span class="s-icon">🛟</span>
                        <h3>Already a member?</h3>
                        <p>Remember: 14-day money-back guarantee, no questions asked. Billing questions go straight to the front of the queue.</p>
                    </div>
                    <div class="side-card" data-reveal style="--rd:.3s">
                        <span class="s-icon">🇹🇹</span>
                        <h3>Where we are</h3>
                        <p>A small family team in Trinidad &amp; Tobago — <a href="{{ route('about') }}">read our story</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
