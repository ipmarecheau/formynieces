@extends('layouts.public')

@section('title', 'About SmoothSeas — SEA prep built for Caribbean families')
@section('description', 'Why we built SmoothSeas: a calmer, kinder way to prepare Caribbean children for the SEA exam, with Smooth the turtle guiding every day and honest weekly reports for parents.')

@section('styles')
<style>
    .about-hero { text-align: center; padding: 84px 0 40px; }
    .about-hero h1 {
        font-family: 'Fredoka One', cursive; font-size: clamp(30px, 5.4vw, 48px); line-height: 1.16;
        background: linear-gradient(135deg, #ecfeff 0%, var(--aqua) 45%, #fcd34d 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        margin-bottom: 18px;
    }
    .about-hero p { font-size: 18px; line-height: 1.75; color: var(--muted); max-width: 640px; margin: 0 auto; }
    .about-smooth { width: 190px; max-width: 50vw; margin: 34px auto 0; display: block; filter: drop-shadow(0 14px 28px rgba(0,0,0,.4)); animation: aboutBob 5s ease-in-out infinite; }
    @keyframes aboutBob { 0%,100% { transform: translateY(0) rotate(-1deg); } 50% { transform: translateY(-10px) rotate(1deg); } }

    .story { background: var(--card); border: 1.5px solid var(--border); border-radius: 22px; padding: 34px 32px; }
    .story p { font-size: 16.5px; line-height: 1.85; color: var(--muted); margin-bottom: 18px; }
    .story p:last-child { margin-bottom: 0; }
    .story strong { color: var(--text); }
    .story .drop::first-letter { font-family: 'Fredoka One', cursive; font-size: 3.1em; color: var(--gold); float: left; line-height: .85; margin: 4px 10px 0 0; }

    .values-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
    .value-card { background: var(--card); border: 1.5px solid var(--border); border-radius: 20px; padding: 26px 24px; transition: border-color .25s, transform .25s; }
    .value-card:hover { border-color: rgba(34,211,238,.6); transform: translateY(-4px); }
    .value-card .v-icon { font-size: 26px; display: block; margin-bottom: 12px; }
    .value-card h3 { font-family: 'Fredoka One', cursive; font-size: 17px; margin-bottom: 8px; color: var(--aqua); }
    .value-card p { font-size: 14.5px; line-height: 1.7; color: var(--dim); }

    .turtle-card { display: grid; grid-template-columns: 210px 1fr; gap: 36px; align-items: center; background: var(--card2); border: 1.5px solid var(--border); border-radius: 22px; padding: 32px; }
    .turtle-card img { width: 100%; filter: drop-shadow(0 12px 24px rgba(0,0,0,.4)); animation: aboutBob 5.4s ease-in-out infinite; }
    .turtle-card h3 { font-family: 'Fredoka One', cursive; font-size: 21px; margin-bottom: 12px; }
    .turtle-card p { font-size: 15.5px; line-height: 1.8; color: var(--muted); margin-bottom: 12px; }
    .turtle-card p:last-child { margin-bottom: 0; }

    .about-cta { text-align: center; background: linear-gradient(135deg, rgba(34,211,238,.25), rgba(246,183,30,.2)); border: 1.5px solid rgba(34,211,238,.4); border-radius: 24px; padding: 52px 32px; }
    .about-cta h2 { font-family: 'Fredoka One', cursive; font-size: clamp(24px, 5vw, 34px); margin-bottom: 14px; background: linear-gradient(135deg, #ecfeff, var(--aqua)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .about-cta p { color: var(--muted); font-size: 16.5px; line-height: 1.65; max-width: 520px; margin: 0 auto 30px; }

    @media (max-width: 720px) {
        .values-grid { grid-template-columns: 1fr; }
        .turtle-card { grid-template-columns: 1fr; text-align: center; }
        .turtle-card img { max-width: 180px; margin: 0 auto; }
    }
</style>
@endsection

@section('content')
    <!-- HERO -->
    <section class="about-hero">
        <div class="container">
            <p class="section-label" data-reveal>About SmoothSeas</p>
            <h1 data-reveal style="--rd:.08s">We started this for two little girls.<br>Then we kept going.</h1>
            <p data-reveal style="--rd:.16s">
                SmoothSeas began as a gift — a learning companion built by an uncle for his two nieces
                preparing for the SEA in Trinidad &amp; Tobago. It grew into a platform for every
                Caribbean family sailing the same waters.
            </p>
            <img class="about-smooth" src="{{ asset('images/voyage/companion/smooth-chart.webp') }}" alt="Smooth the turtle, charting the course" data-reveal style="--rd:.22s">
        </div>
    </section>

    <!-- STORY -->
    <section>
        <div class="container">
            <div class="story" data-reveal>
                <p class="drop">Like most Caribbean parents, our founder watched the same cycle every year: expensive workbooks abandoned by February, extra lessons that teach to the test, and report cards that arrive too late to change anything. Nobody could answer the only question that mattered — <strong>how is my child actually doing, and what should we do tonight?</strong></p>
                <p>So he built the answer for his own family first. A platform that plans the whole SEA journey — <strong>Math, English Language Arts and Writing</strong> — re-plans it every single day around each child, reports honestly to the parent each week, and makes the child <strong>want</strong> to log in by putting a warm, patient companion named Smooth at the helm.</p>
                <p>Everything you see on this site was built for two real children first — our nieces — and only then offered to yours. We are a small, family-run team in Trinidad &amp; Tobago. We make no claims we have not watched happen at our own kitchen table.</p>
            </div>
        </div>
    </section>

    <div class="divider"></div>

    <!-- VALUES -->
    <section>
        <div class="container">
            <p class="section-label" data-reveal>What we believe</p>
            <h2 class="section-title" data-reveal style="--rd:.08s">Four promises we build by.</h2>
            <p class="section-sub" data-reveal style="--rd:.16s">They shape every screen your child sees — and every report you read.</p>

            <div class="values-grid">
                <div class="value-card" data-reveal>
                    <span class="v-icon">🧭</span>
                    <h3>Honest progress, always</h3>
                    <p>You get the truth every week — real pace, real gaps, no rosy spin. A parent who knows exactly where the child stands can actually help.</p>
                </div>
                <div class="value-card" data-reveal style="--rd:.08s">
                    <span class="v-icon">💛</span>
                    <h3>No shame in re-learning</h3>
                    <p>The child never sees red-ink percentages or failure screens. A missed rule triggers a gentle re-teach with Smooth — never a scolding.</p>
                </div>
                <div class="value-card" data-reveal style="--rd:.16s">
                    <span class="v-icon">🏫</span>
                    <h3>We work with schools</h3>
                    <p>We complement the classroom, never compete with it. Graded school papers join the child's journal so one picture forms — at home and at school.</p>
                </div>
                <div class="value-card" data-reveal style="--rd:.24s">
                    <span class="v-icon">🇹🇹</span>
                    <h3>Caribbean first</h3>
                    <p>Built in Trinidad &amp; Tobago for the SEA — our words, our context, our exam. Not a foreign course with a flag stickered on.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="divider"></div>

    <!-- WHY A TURTLE -->
    <section>
        <div class="container">
            <div class="turtle-card" data-reveal>
                <img src="{{ asset('images/voyage/companion/smooth.webp') }}" alt="Smooth the turtle, waving">
                <div>
                    <h3>Why a turtle?</h3>
                    <p>Because the SEA is a crossing, and nobody crosses an ocean in a sprint. A turtle is steady, calm, and carries its home on its back — it never panics, and it never stops.</p>
                    <p>That is exactly how we want a child to feel on exam day: <strong>prepared, unhurried, and sure of the way.</strong> So the companion who walks them through every lesson is a turtle named Smooth.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section>
        <div class="container">
            <div class="about-cta" data-reveal>
                <h2>Come meet the crew.</h2>
                <p>
                    Fifteen minutes on the phone with us and you will know exactly how the voyage would
                    look for your child — no pressure, no obligation.
                </p>
                <a class="btn-primary" href="{{ route('book.call') }}">Book a free 15-minute call</a>
            </div>
        </div>
    </section>
@endsection
