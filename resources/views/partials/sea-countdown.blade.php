{{-- Floating flip-calendar countdown to the SEA exam — top-left, always ticking.
     Adjust the target date in data-target (local time). --}}
<style>
    .sea-cd {
        position: fixed; top: 80px; left: 14px; z-index: 90;
        background: linear-gradient(165deg, #0a2036, #0d2c47);
        border: 1px solid rgba(120,170,220,.28); border-radius: 14px;
        padding: 10px 11px 11px; box-shadow: 0 14px 34px rgba(6,20,40,.4);
        width: 168px; color: #eaf4fb;
    }
    .sea-cd-head {
        display: flex; align-items: center; gap: 6px; font-family: 'Fredoka', sans-serif;
        font-weight: 600; font-size: 12.5px; color: #ffd15c; letter-spacing: .02em; margin-bottom: 8px;
    }
    .sea-cd-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; }
    .sc-tile {
        position: relative; background: #071726; border: 1px solid rgba(120,170,220,.18);
        border-radius: 8px; padding: 7px 0 5px; text-align: center; overflow: hidden;
        box-shadow: inset 0 -2px 4px rgba(0,0,0,.45), inset 0 1px 0 rgba(255,255,255,.04);
    }
    .sc-tile::after { /* the clip divider line */
        content: ''; position: absolute; left: 5px; right: 5px; top: 62%; height: 1px;
        background: rgba(0,0,0,.55); box-shadow: 0 1px 0 rgba(255,255,255,.04);
    }
    .sc-tile b {
        display: block; font-family: 'Fredoka', sans-serif; font-weight: 700; font-size: 20px;
        line-height: 1; color: #fff; font-variant-numeric: tabular-nums;
    }
    .sc-tile span {
        display: block; margin-top: 3px; font-size: 8.5px; font-weight: 800; letter-spacing: .1em;
        text-transform: uppercase; color: #8fa6bd;
    }
    .sc-tile.flip b { animation: scFlip .5s ease; }
    @keyframes scFlip { 0%{transform:rotateX(0)} 45%{transform:rotateX(-88deg); opacity:.35} 55%{transform:rotateX(88deg); opacity:.35} 100%{transform:rotateX(0); opacity:1} }
    @media (max-width: 760px) {
        .sea-cd { width: 132px; top: 74px; left: 8px; padding: 8px; }
        .sc-tile b { font-size: 16px; } .sc-tile span { font-size: 7.5px; }
    }
    @media (prefers-reduced-motion: reduce) { .sc-tile.flip b { animation: none; } }
</style>

<aside class="sea-cd" data-target="2027-03-25T09:00:00" aria-label="Countdown to the SEA exam">
    <div class="sea-cd-head">⏳ Countdown to SEA</div>
    <div class="sea-cd-grid">
        <div class="sc-tile"><b id="scMonths">0</b><span>Mon</span></div>
        <div class="sc-tile"><b id="scWeeks">0</b><span>Wks</span></div>
        <div class="sc-tile"><b id="scDays">0</b><span>Days</span></div>
        <div class="sc-tile"><b id="scHours">0</b><span>Hrs</span></div>
        <div class="sc-tile"><b id="scMins">0</b><span>Min</span></div>
        <div class="sc-tile"><b id="scSecs">0</b><span>Sec</span></div>
    </div>
</aside>

<script>
    (function () {
        var root = document.querySelector('.sea-cd'); if (!root) { return; }
        var target = new Date(root.getAttribute('data-target'));
        var els = {
            months: document.getElementById('scMonths'), weeks: document.getElementById('scWeeks'),
            days: document.getElementById('scDays'), hours: document.getElementById('scHours'),
            mins: document.getElementById('scMins'), secs: document.getElementById('scSecs'),
        };
        var DAY = 86400000, HR = 3600000, MIN = 60000;

        function breakdown() {
            var now = new Date();
            if (target <= now) { return { months: 0, weeks: 0, days: 0, hours: 0, mins: 0, secs: 0 }; }
            // whole calendar months first, then the remainder
            var cursor = new Date(now), months = 0;
            while (true) { var t = new Date(cursor); t.setMonth(t.getMonth() + 1); if (t <= target) { cursor = t; months++; } else { break; } }
            var rem = target - cursor;
            var totalDays = Math.floor(rem / DAY);
            var weeks = Math.floor(totalDays / 7), days = totalDays % 7;
            rem -= totalDays * DAY;
            var hours = Math.floor(rem / HR); rem -= hours * HR;
            var mins = Math.floor(rem / MIN); rem -= mins * MIN;
            var secs = Math.floor(rem / 1000);
            return { months: months, weeks: weeks, days: days, hours: hours, mins: mins, secs: secs };
        }
        function set(el, val) {
            var s = String(val);
            if (el.textContent !== s) {
                el.textContent = s;
                var tile = el.parentNode; tile.classList.remove('flip'); void tile.offsetWidth; tile.classList.add('flip');
            }
        }
        function tick() {
            var b = breakdown();
            set(els.months, b.months); set(els.weeks, b.weeks); set(els.days, b.days);
            set(els.hours, b.hours); set(els.mins, b.mins); set(els.secs, b.secs);
        }
        tick(); setInterval(tick, 1000);
    })();
</script>
