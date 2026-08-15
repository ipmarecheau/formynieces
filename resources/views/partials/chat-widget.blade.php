@php
    $waNumber = '18683234443';
    $waLink = 'https://wa.me/'.$waNumber.'?text='.rawurlencode("Ahoy! I'm on the SmoothSeas site and I have a question about the voyage for my child.");
@endphp
@guest
<style>
    /* ── SMOOTH CHAT WIDGET (LC-01..06) ── */
    .scw-bubble {
        position: fixed; right: 20px; bottom: 20px; z-index: 90;
        width: 60px; height: 60px; border-radius: 50%;
        border: 3px solid #67e8f9; background: #0c2440; padding: 4px;
        cursor: pointer; box-shadow: 0 6px 18px rgba(0,0,0,.4);
        transition: transform .15s;
    }
    .scw-bubble:hover { transform: scale(1.08); }
    .scw-bubble img { width: 100%; height: 100%; object-fit: contain; }
    .scw-bubble-dot {
        position: absolute; top: -2px; right: -2px; width: 14px; height: 14px;
        border-radius: 50%; background: #f6b71e; border: 2px solid #0b2a4a;
        animation: scwPulse 2s ease-in-out infinite;
    }
    @keyframes scwPulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.25); } }

    .scw-invite {
        position: fixed; right: 20px; bottom: 92px; z-index: 90;
        max-width: 280px; background: #f0fbff; color: #0b2a4a;
        border-radius: 16px; border-bottom-right-radius: 4px;
        padding: 13px 16px; font-size: 14px; font-weight: 700; line-height: 1.45;
        box-shadow: 0 12px 30px rgba(0,0,0,.35);
        opacity: 0; transform: translateY(10px); pointer-events: none;
        transition: opacity .35s ease, transform .35s ease;
    }
    .scw-invite.show { opacity: 1; transform: none; pointer-events: auto; }
    .scw-invite .scw-invite-x {
        position: absolute; top: -9px; left: -9px; width: 22px; height: 22px;
        border-radius: 50%; border: none; background: #0b2a4a; color: #67e8f9;
        font-size: 12px; font-weight: 800; cursor: pointer; line-height: 1;
    }

    .scw-panel {
        position: fixed; right: 20px; bottom: 92px; z-index: 95;
        width: 340px; max-width: calc(100vw - 32px); height: 460px; max-height: calc(100vh - 130px);
        background: #081c33; border: 1.5px solid rgba(103,232,249,.4); border-radius: 20px;
        display: flex; flex-direction: column; overflow: hidden;
        box-shadow: 0 24px 60px rgba(0,0,0,.5);
        opacity: 0; transform: translateY(14px) scale(.98); pointer-events: none;
        transition: opacity .3s ease, transform .3s ease;
    }
    .scw-panel.open { opacity: 1; transform: none; pointer-events: auto; }
    .scw-head {
        display: flex; align-items: center; gap: 10px; padding: 12px 14px;
        background: #0c2440; border-bottom: 1.5px solid rgba(103,232,249,.25);
    }
    .scw-head img { width: 38px; height: 38px; object-fit: contain; }
    .scw-head .t { font-family: 'Fredoka One', cursive; font-size: 15px; color: #67e8f9; }
    .scw-head .s { font-size: 11px; color: #5eead4; font-weight: 700; }
    .scw-head .x {
        margin-left: auto; background: none; border: none; color: #93b2cc;
        font-size: 18px; cursor: pointer; padding: 4px 8px; border-radius: 8px;
    }
    .scw-head .x:hover { color: #e6f2fb; background: rgba(103,232,249,.1); }
    .scw-log { flex: 1; overflow-y: auto; padding: 14px; display: flex; flex-direction: column; gap: 10px; }
    .scw-line { max-width: 85%; padding: 10px 13px; border-radius: 14px; font-size: 13.8px; line-height: 1.5; }
    .scw-line.bot { align-self: flex-start; background: #0c2440; color: #e6f2fb; border: 1.5px solid rgba(103,232,249,.2); border-bottom-left-radius: 4px; }
    .scw-line.visitor { align-self: flex-end; background: linear-gradient(135deg, #0e7490, #f6b71e); color: #fff; border-bottom-right-radius: 4px; }
    .scw-form { padding: 10px 14px 14px; border-top: 1.5px solid rgba(103,232,249,.2); }
    .scw-form .row { display: flex; gap: 8px; }
    .scw-form input {
        flex: 1; background: #0c2440; border: 1.5px solid rgba(103,232,249,.3); border-radius: 999px;
        padding: 10px 15px; color: #e6f2fb; font-family: 'Nunito', sans-serif; font-size: 14px; outline: none;
    }
    .scw-form input:focus { border-color: rgba(103,232,249,.6); }
    .scw-form button {
        border: none; border-radius: 999px; padding: 10px 18px; cursor: pointer;
        background: linear-gradient(135deg, #0e7490, #f6b71e); color: #fff;
        font-family: 'Fredoka One', cursive; font-size: 14px;
    }
    .scw-btns { display: flex; flex-wrap: wrap; gap: 8px; padding: 10px 14px 14px; }
    .scw-btn {
        flex: 1 1 40%; text-align: center; text-decoration: none; cursor: pointer;
        border-radius: 999px; padding: 10px 12px; font-size: 12.8px; font-weight: 800;
        font-family: 'Nunito', sans-serif;
    }
    .scw-btn.wa { background: rgba(37,211,102,.15); border: 1.5px solid rgba(37,211,102,.5); color: #4ade80; }
    .scw-btn.call { background: linear-gradient(135deg, #0e7490, #f6b71e); color: #fff; }
    .scw-btn.plain { background: none; border: 1.5px solid rgba(103,232,249,.3); color: #93b2cc; }
    .scw-typing { display: inline-flex; gap: 4px; padding: 4px 2px; }
    .scw-typing i { width: 6px; height: 6px; border-radius: 50%; background: #67e8f9; animation: scwDot 1s ease-in-out infinite; }
    .scw-typing i:nth-child(2) { animation-delay: .15s; } .scw-typing i:nth-child(3) { animation-delay: .3s; }
    @keyframes scwDot { 0%,100% { opacity: .3; transform: translateY(0); } 50% { opacity: 1; transform: translateY(-3px); } }
    @media (prefers-reduced-motion: reduce) { .scw-bubble-dot, .scw-typing i { animation: none; } }
</style>

<button type="button" class="scw-bubble" id="scwBubble" aria-label="Chat with Smooth" hidden>
    <img src="{{ asset('images/voyage/companion/smooth.webp') }}" alt="Smooth the turtle">
    <span class="scw-bubble-dot"></span>
</button>

<div class="scw-invite" id="scwInvite" role="dialog" aria-label="Smooth invites you to chat">
    <button type="button" class="scw-invite-x" id="scwInviteX" aria-label="Dismiss">✕</button>
    Ahoy! 👋 Got a question about the voyage for your child? I'm right here.
</div>

<div class="scw-panel" id="scwPanel" role="dialog" aria-label="Chat with Smooth">
    <div class="scw-head">
        <img src="{{ asset('images/voyage/companion/smooth.webp') }}" alt="">
        <div>
            <div class="t">Smooth</div>
            <div class="s">Usually replies within a few hours</div>
        </div>
        <button type="button" class="x" id="scwClose" aria-label="Close chat">✕</button>
    </div>
    <div class="scw-log" id="scwLog"></div>
    <div class="scw-form" id="scwForm">
        <div class="row">
            <input type="text" id="scwInput" placeholder="Type your answer…" autocomplete="off">
            <button type="button" id="scwSend">Send</button>
        </div>
    </div>
    <div class="scw-btns" id="scwBtns" hidden>
        <a class="scw-btn wa" href="{{ $waLink }}" target="_blank" rel="noopener">Chat on WhatsApp</a>
        <a class="scw-btn call" href="{{ route('book.call') }}">Book a free call</a>
        <a class="scw-btn plain" href="#" id="scwDone">That's it, thanks</a>
    </div>
</div>

<script>
(function () {
    var bubble = document.getElementById('scwBubble'),
        invite = document.getElementById('scwInvite'),
        inviteX = document.getElementById('scwInviteX'),
        panel = document.getElementById('scwPanel'),
        close = document.getElementById('scwClose'),
        log = document.getElementById('scwLog'),
        form = document.getElementById('scwForm'),
        input = document.getElementById('scwInput'),
        send = document.getElementById('scwSend'),
        btns = document.getElementById('scwBtns'),
        done = document.getElementById('scwDone');

    var SEEN_KEY = 'ss_chat_dismissed_at', COOLDOWN_DAYS = 30;
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var convo = { id: null, name: null, standard: null, worry: null, contact: null, step: 0, done: false };

    // The scripted qualification (LC-02). Steps: 1 name, 2 standard, 3 worry, 4 contact, 5 wrap.
    var botLines = {
        0: "Ahoy! I'm Smooth 🐢 I look after the voyages here. Got a question about the voyage for your child? I'll take down a few notes and the founder — a real human — will reply personally.",
        1: "First — what's your name?",
        2: "Lovely to meet you, {name}! Which standard is your child in?",
        3: "And what's the biggest thing on your mind right now — Math, ELA, Writing, timing, cost…? (One line is plenty, or type 'skip'.)",
        4: "Last thing: where should we reach you — an email or a WhatsApp number?",
        5: "Thank you, {name}! ⛵ The founder will get back to you within a few hours — it's a small family team, so never days. Want to talk sooner? WhatsApp or a free call works right now."
    };

    function seen() {
        try { return (Date.now() - (parseInt(localStorage.getItem(SEEN_KEY) || '0', 10) || 0)) < COOLDOWN_DAYS * 86400000; } catch (e) { return false; }
    }
    function markSeen() { try { localStorage.setItem(SEEN_KEY, String(Date.now())); } catch (e) {} }

    function el(tag, cls, text) {
        var n = document.createElement(tag);
        if (cls) { n.className = cls; }
        if (text !== undefined) { n.textContent = text; }
        return n;
    }

    function botSay(text, instant) {
        if (instant || reduced) {
            log.appendChild(el('div', 'scw-line bot', text));
            log.scrollTop = log.scrollHeight;
            return;
        }
        var typing = el('div', 'scw-line bot');
        var dots = el('span', 'scw-typing');
        dots.appendChild(el('i')); dots.appendChild(el('i')); dots.appendChild(el('i'));
        typing.appendChild(dots);
        log.appendChild(typing);
        log.scrollTop = log.scrollHeight;
        setTimeout(function () {
            typing.replaceChildren();
            typing.textContent = text;
            log.scrollTop = log.scrollHeight;
        }, 700);
    }

    function visitorEcho(text) {
        log.appendChild(el('div', 'scw-line visitor', text));
        log.scrollTop = log.scrollHeight;
    }

    function ask(step) {
        convo.step = step;
        var line = botLines[step].replace('{name}', convo.name || 'friend');
        botSay(line);
        if (step === 2) {
            ['Standard 3', 'Standard 4', 'Standard 5', 'Not sure yet'].forEach(function (s) {
                var chip = el('button', 'scw-btn plain', s);
                chip.style.flex = '1 1 40%';
                chip.addEventListener('click', function () { handleAnswer(s); });
                btns.appendChild(chip);
            });
            form.hidden = true; btns.hidden = false;
        } else if (step === 5) {
            form.hidden = true; btns.hidden = false;
        }
    }

    function handleAnswer(value) {
        visitorEcho(value);
        if (convo.step === 1) { convo.name = value; }
        if (convo.step === 2) { convo.standard = value; }
        if (convo.step === 3) { convo.worry = (value.toLowerCase() === 'skip') ? null : value; }
        if (convo.step === 4) { convo.contact = value; }
        post(value);
        var next = convo.step + 1;
        if (next <= 5) { ask(next); }
    }

    function post(body) {
        var payload = { body: body };
        if (convo.id) { payload.id = convo.id; }
        ['name', 'standard', 'worry', 'contact'].forEach(function (k) {
            var v = convo[{ name: 'visitor_name', standard: 'child_standard', worry: 'worry', contact: 'contact' }[k]];
            if (v) { payload[k] = v; }
        });
        fetch(convo.id ? '{{ route('chat.message') }}' : '{{ route('chat.start') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload)
        }).then(function (r) { return r.json(); }).then(function (d) {
            if (d.id) { convo.id = d.id; post(convo.name || 'started the chat'); }
        }).catch(function () { /* never break the page (LC-04) */ });
    }

    function openPanel() {
        invite.classList.remove('show');
        panel.classList.add('open');
        bubble.hidden = false;
        if (convo.step === 0) { ask(0); setTimeout(function () { ask(1); }, 1200); convo.step = 1; }
        input.focus();
    }

    bubble.addEventListener('click', openPanel);
    close.addEventListener('click', function () { panel.classList.remove('open'); markSeen(); });
    inviteX.addEventListener('click', function () { invite.classList.remove('show'); markSeen(); });
    done.addEventListener('click', function (e) { e.preventDefault(); panel.classList.remove('open'); markSeen(); });

    function submit() {
        var v = input.value.trim();
        if (!v) { return; }
        input.value = '';
        handleAnswer(v);
    }
    send.addEventListener('click', submit);
    input.addEventListener('keydown', function (e) { if (e.key === 'Enter') { submit(); } });

    // Boot: bubble always available; proactive invite once per cooldown, after ~35s or half-scroll (LC-01).
    bubble.hidden = false;
    if (!seen()) {
        var shown = false, timer = null;
        function pop() {
            if (shown || panel.classList.contains('open')) { return; }
            shown = true;
            invite.classList.add('show');
            setTimeout(function () { invite.classList.remove('show'); }, 14000);
        }
        timer = setTimeout(pop, 35000);
        window.addEventListener('scroll', function onScroll() {
            if ((window.scrollY / Math.max(1, document.body.scrollHeight - window.innerHeight)) > 0.45) {
                window.removeEventListener('scroll', onScroll);
                clearTimeout(timer);
                pop();
            }
        }, { passive: true });
        invite.addEventListener('click', function (e) { if (e.target !== inviteX) { openPanel(); } });
    }
})();
</script>
@endguest
