{{-- Sticky top urgency bar: full-width countdown to the SEA exam + CTA. Mobile-safe
     (single line, no overlap). Adjust the target date in data-target (local time). --}}
<style>
    .sea-bar {
        position: sticky; top: 0; z-index: 101; height: 42px;
        display: flex; align-items: center; justify-content: center; gap: 14px;
        background: linear-gradient(90deg, #0a5c68, #0d7d8c);
        color: #fff; white-space: nowrap; overflow: hidden; padding: 0 14px;
        font-family: 'Nunito', system-ui, sans-serif; font-weight: 700; font-size: 14px;
        box-shadow: 0 2px 10px rgba(10,60,72,.25);
    }
    .sea-bar .sb-lbl { font-family: 'Fredoka', sans-serif; font-weight: 600; }
    .sea-bar .sb-cd { font-variant-numeric: tabular-nums; letter-spacing: .01em; }
    .sea-bar .sb-cd b { color: #ffd15c; font-family: 'Fredoka', sans-serif; font-weight: 700; }
    .sea-bar .sb-cta {
        flex: none; background: var(--amber, #f2a900); color: #3a2900; text-decoration: none;
        font-family: 'Fredoka', sans-serif; font-weight: 600; font-size: 13px;
        padding: 5px 15px; border-radius: 999px; transition: background .2s, transform .12s;
    }
    .sea-bar .sb-cta:hover { background: #ffbc1f; transform: translateY(-1px); }
    @media (max-width: 640px) {
        .sea-bar { height: 40px; gap: 9px; font-size: 12.5px; padding: 0 10px; }
        .sea-bar .sb-lbl-full { display: none; }
        .sea-bar .sb-cta { padding: 5px 12px; font-size: 12.5px; }
        header.nav { top: 40px; }   /* keep the sticky nav flush under the shorter bar */
    }
    @media (max-width: 380px) { .sea-bar .sb-secs-wrap { display: none; } }
</style>

<div class="sea-bar" data-target="2027-03-25T09:00:00" role="region" aria-label="Countdown to the SEA exam">
    <span class="sb-lbl">🔥 <span class="sb-lbl-full">SEA 2027 in</span></span>
    <span class="sb-cd"><b id="scMonths">0</b>mo <b id="scWeeks">0</b>w <b id="scDays">0</b>d
        · <b id="scHours">0</b>:<b id="scMins">00</b><span class="sb-secs-wrap">:<b id="scSecs">00</b></span></span>
    <a class="sb-cta" href="{{ route('register') }}">Start free →</a>
</div>

<script>
    (function () {
        var root = document.querySelector('.sea-bar'); if (!root) { return; }
        var target = new Date(root.getAttribute('data-target'));
        var g = function (id) { return document.getElementById(id); };
        var els = { months: g('scMonths'), weeks: g('scWeeks'), days: g('scDays'), hours: g('scHours'), mins: g('scMins'), secs: g('scSecs') };
        var DAY = 86400000, HR = 3600000, MIN = 60000;
        var pad = function (n) { return n < 10 ? '0' + n : '' + n; };

        function breakdown() {
            var now = new Date();
            if (target <= now) { return { months: 0, weeks: 0, days: 0, hours: 0, mins: 0, secs: 0 }; }
            var cursor = new Date(now), months = 0;
            while (true) { var t = new Date(cursor); t.setMonth(t.getMonth() + 1); if (t <= target) { cursor = t; months++; } else { break; } }
            var rem = target - cursor;
            var totalDays = Math.floor(rem / DAY);
            var weeks = Math.floor(totalDays / 7), days = totalDays % 7;
            rem -= totalDays * DAY;
            var hours = Math.floor(rem / HR); rem -= hours * HR;
            var mins = Math.floor(rem / MIN); rem -= mins * MIN;
            return { months: months, weeks: weeks, days: days, hours: hours, mins: mins, secs: Math.floor(rem / 1000) };
        }
        function tick() {
            var b = breakdown();
            els.months.textContent = b.months; els.weeks.textContent = b.weeks; els.days.textContent = b.days;
            els.hours.textContent = b.hours; els.mins.textContent = pad(b.mins); els.secs.textContent = pad(b.secs);
        }
        tick(); setInterval(tick, 1000);
    })();
</script>
