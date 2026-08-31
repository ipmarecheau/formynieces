@extends('layouts.public')

@section('title', 'SmoothSeas FAQ — SEA exam prep questions, answered')
@section('description', 'Answers to the questions Caribbean parents ask about SEA preparation with SmoothSeas — how the daily plan works, what it costs, and how your child stays on pace.')

@section('styles')
<style>
    .faq-hero { text-align: center; padding: 76px 0 40px; }
    .faq-hero h1 {
        font-family: 'Fredoka One', cursive; font-size: clamp(30px, 5.4vw, 46px);
        background: linear-gradient(135deg, #ecfeff 0%, var(--aqua) 45%, #fcd34d 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        margin-bottom: 16px;
    }
    .faq-hero p { font-size: 17.5px; line-height: 1.7; color: var(--muted); max-width: 560px; margin: 0 auto; }

    .faq-group-label { font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: var(--gold); margin: 40px 0 14px; }

    details {
        background: var(--card); border: 1.5px solid var(--border); border-radius: 16px;
        margin-bottom: 10px; overflow: hidden; transition: border-color .25s;
    }
    details[open] { border-color: rgba(34,211,238,.55); }
    details summary {
        list-style: none; cursor: pointer; user-select: none;
        display: flex; align-items: center; justify-content: space-between; gap: 14px;
        padding: 17px 20px; font-weight: 800; font-size: 15.5px; color: var(--text);
    }
    details summary::-webkit-details-marker { display: none; }
    details summary .chev { flex-shrink: 0; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(34,211,238,.14); color: var(--aqua); font-weight: 800; transition: transform .25s; }
    details[open] summary .chev { transform: rotate(45deg); }
    details .faq-a { padding: 0 20px 19px; font-size: 14.8px; line-height: 1.75; color: var(--dim); }
    details .faq-a strong { color: var(--text); }
    details .faq-a a { color: var(--aqua); }

    .faq-cta { text-align: center; background: linear-gradient(135deg, rgba(34,211,238,.25), rgba(246,183,30,.2)); border: 1.5px solid rgba(34,211,238,.4); border-radius: 24px; padding: 48px 32px; }
    .faq-cta h2 { font-family: 'Fredoka One', cursive; font-size: clamp(22px, 4.6vw, 32px); margin-bottom: 12px; background: linear-gradient(135deg, #ecfeff, var(--aqua)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .faq-cta p { color: var(--muted); font-size: 16px; line-height: 1.65; max-width: 520px; margin: 0 auto 28px; }
</style>
@endsection

@section('content')
    <section class="faq-hero">
        <div class="container">
            <h1 data-reveal>Questions parents ask us.</h1>
            <p data-reveal style="--rd:.08s">Straight answers — no fine print. Anything we missed, ask us directly on a free call.</p>
        </div>
    </section>

    <section>
        <div class="container">
            <p class="faq-group-label" data-reveal>Getting started</p>
            <details data-reveal>
                <summary>What exactly is SmoothSeas? <span class="chev">+</span></summary>
                <div class="faq-a">An online SEA preparation companion for Caribbean children. It plans a personalised study journey across <strong>Math, English Language Arts and Writing</strong>, adjusts that plan every single day to how your child is doing, teaches through interactive lessons and gamified practice, and reports to you — the parent — every week. The captain is <strong>Smooth</strong>, a patient turtle who guides your child through every screen.</div>
            </details>
            <details data-reveal>
                <summary>Who is it for? <span class="chev">+</span></summary>
                <div class="faq-a">Children in Standards 3–5 (roughly ages 9–11) preparing for the SEA. It works whether your child is <strong>on track, behind, or already ahead</strong> — the plan starts from a friendly diagnostic that finds where they truly are, not where a workbook assumes they should be.</div>
            </details>
            <details data-reveal>
                <summary>When should we start? <span class="chev">+</span></summary>
                <div class="faq-a">Ideally a year or more before the exam so the journey is calm and steady. Starting later is fine too — the platform compresses the route sensibly and prioritises what matters most for the remaining weeks. For the SEA 2027 class, starting now is perfect timing.</div>
            </details>
            <details data-reveal>
                <summary>Does it replace school or extra lessons? <span class="chev">+</span></summary>
                <div class="faq-a">No — it <strong>works with them</strong>. School teaches; SmoothSeas plans, personalises and reinforces. Many families use it instead of juggling workbooks and extra lessons; others keep lessons and use SmoothSeas as the daily structure between them. What it replaces is the guessing: what to do today, and whether it is working.</div>
            </details>

            <p class="faq-group-label" data-reveal>Learning &amp; progress</p>
            <details data-reveal>
                <summary>How does it know what my child needs? <span class="chev">+</span></summary>
                <div class="faq-a">Every child starts with a friendly diagnostic — low-stress, no timer, more like a game than a test. From that, the platform maps their whole journey, then <strong>re-plans it daily</strong>: breeze through a topic and they advance; struggle and it circles back gently. You never have to decide what comes next.</div>
            </details>
            <details data-reveal>
                <summary>My child is behind. Will this make them feel bad? <span class="chev">+</span></summary>
                <div class="faq-a">Never — this is the heart of the design. Your child never sees percentages, deficits or red-ink marks. When a rule is missed, <strong>Smooth re-teaches that exact rule</strong>, word by word, patiently, with no scolding. Catch-up pacing happens quietly in the background; streaks restart without shame. The pressure lives in your Parent Portal, where it belongs — with you, not on them.</div>
            </details>
            <details data-reveal>
                <summary>My child is already strong. Is this still useful? <span class="chev">+</span></summary>
                <div class="faq-a">Yes — uneven profiles are normal (strong in Math, wobbly in Writing, for example). Children who breeze through advance quickly instead of waiting, and the plan quietly routes extra practice at the strands that need it. Being ahead just means the voyage moves faster.</div>
            </details>
            <details data-reveal>
                <summary>How much time does it take each day? <span class="chev">+</span></summary>
                <div class="faq-a">The daily plan is <strong>flexible: as little as 20 minutes or as much as two full hours</strong> of guided learning — you choose the pace that fits your family. Practice time is always <strong>unlimited</strong> for children who want more. The morning ritual — a short vocabulary session and a reading assignment — anchors the day.</div>
            </details>
            <details data-reveal>
                <summary>What does a re-teach actually look like? <span class="chev">+</span></summary>
                <div class="faq-a">Say the rule is plurals with consonant + y. Smooth explains the rule in one sentence, then they practise it word by word — type it, get it gently corrected, say it back. After a few wins they return to the normal voyage. If a rule needs three tries, the lesson is simply marked <strong>in progress</strong> — never failed — and resurfaces until it clicks. You see every re-teach in your weekly report.</div>
            </details>

            <p class="faq-group-label" data-reveal>For parents</p>
            <details data-reveal>
                <summary>How do I track progress? <span class="chev">+</span></summary>
                <div class="faq-a">Two ways. A <strong>weekly report</strong> summarises what was conquered, what needed re-teaching, and whether the voyage is on pace — written plainly, no jargon. The <strong>Parent Portal</strong> gives you the same depth any day you want it, strand by strand. Progress is always honest — we would rather tell you a hard truth early than a comfortable lie late.</div>
            </details>
            <details data-reveal>
                <summary>What happens when life happens — travel, illness, exams? <span class="chev">+</span></summary>
                <div class="faq-a">Pause with one tap and resume the same way. Pausing <strong>freezes streaks</strong> so nothing is lost, and the plan re-paces itself around the gap — no guilt, and no impossible catch-up cliff waiting on the other side.</div>
            </details>
            <details data-reveal>
                <summary>I worry about screen time. <span class="chev">+</span></summary>
                <div class="faq-a">Healthy concern — we share it. The daily plan is <strong>bounded by design</strong>: it has a clear finish line each day, so sessions end. There are no feeds, no ads, no infinite-scroll traps — the fun is in the learning itself, and the voyage map celebrates <em>finishing</em>, not staying glued.</div>
            </details>
            <details data-reveal>
                <summary>Can both parents (or a grandparent) be involved? <span class="chev">+</span></summary>
                <div class="faq-a">The parent account and the Parent Portal are the family hub today. Read-only access for a second guardian (so both parents, or a grandparent, can follow along) is on our near-term roadmap.</div>
            </details>

            <p class="faq-group-label" data-reveal>Money, tech &amp; safety</p>
            <details data-reveal>
                <summary>How much does it cost? <span class="chev">+</span></summary>
                <div class="faq-a"><strong>$200 per month, per family</strong> — everything included: all three SEA components, the adaptive daily plans, unlimited practice, weekly reports and the Parent Portal. Cancel anytime; no contracts, no hidden fees.</div>
            </details>
            <details data-reveal>
                <summary>What if it does not work for us? <span class="chev">+</span></summary>
                <div class="faq-a">Then you pay nothing. Your child logs in and uses the platform for <strong>14 days</strong>; if you are unsatisfied for <em>any</em> reason, we refund every cent — <strong>no questions asked</strong>. And we put the outcome in writing too: we guarantee you will see <strong>measurable improvement in Math, ELA, Writing and Vocabulary within 14 days or less</strong>.</div>
            </details>
            <details data-reveal>
                <summary>What equipment do we need? <span class="chev">+</span></summary>
                <div class="faq-a">Any modern browser — a tablet, laptop or desktop — and an internet connection. No apps to install, no special devices.</div>
            </details>
            <details data-reveal>
                <summary>Is my child safe on the platform — and what about the AI? <span class="chev">+</span></summary>
                <div class="faq-a">Accounts are private and role-separated: your child sees only their own voyage, and their data is <strong>never sold or shared</strong>. Smooth's brain is an AI companion, and it is governed: strict per-child usage and spend caps, conversations logged and monitored, and any concerning message is flagged for human review. On our side of the screen, a person is always responsible.</div>
            </details>
        </div>
    </section>

    <!-- CTA -->
    <section>
        <div class="container">
            <div class="faq-cta" data-reveal>
                <h2>Still have a question?</h2>
                <p>
                    Fifteen minutes with us beats an hour of scrolling. Ask anything — we will give you
                    the same straight answers you just read.
                </p>
                <a class="btn-primary" href="{{ route('book.call') }}">Book a free 15-minute call</a>
            </div>
        </div>
    </section>
@endsection
