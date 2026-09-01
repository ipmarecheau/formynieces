{{-- Sticky top urgency bar: full-width countdown to the SEA exam + CTA.
     Desktop: one line. Mobile: two lines (label + labelled units). Seconds always tick.
     Adjust the target date in data-target (local time). --}}
<style>
    .sea-bar {
        position: sticky; top: 0; z-index: 101; height: 42px;
        display: flex; align-items: center; justify-content: center; gap: 16px;
        background: linear-gradient(90deg, #0a5c68, #0d7d8c);
        color: #fff; padding: 0 14px; box-shadow: 0 2px 10px rgba(10,60,72,.25);
        font-family: 'Nunito', system-ui, sans-serif; font-weight: 700; font-size: 14px;
    }
    .sea-bar .sb-main { display: flex; align-items: baseline; gap: 10px; min-width: 0; }
    .sea-bar .sb-lbl { font-family: 'Fredoka', sans-serif; font-weight: 600; white-space: nowrap; }
    .sea-bar .sb-lbl-mobile { display: none; }
    .sea-bar .sb-cd { font-variant-numeric: tabular-nums; white-space: nowrap; }
    .sea-bar .sb-cd b { color: #ffd15c; font-family: 'Fredoka', sans-serif; font-weight: 700; display: inline-block; }
    .sea-bar .sb-cd .u { opacity: .8; font-weight: 700; margin: 0 5px 0 1px; }
    .sea-bar #scSecs.flip { animation: sbFlip .5s ease; }
    @keyframes sbFlip { 0%{transform:rotateX(0)} 45%{transform:rotateX(-85deg);opacity:.4} 55%{transform:rotateX(85deg);opacity:.4} 100%{transform:rotateX(0);opacity:1} }
    .sea-bar .sb-cta {
        flex: none; background: var(--amber, #f2a900); color: #3a2900; text-decoration: none;
        font-family: 'Fredoka', sans-serif; font-weight: 600; font-size: 13px;
        padding: 5px 15px; border-radius: 999px; transition: background .2s, transform .12s;
    }
    .sea-bar .sb-cta:hover { background: #ffbc1f; transform: translateY(-1px); }

    @media (max-width: 640px) {
        .sea-bar { height: 54px; gap: 10px; padding: 0 10px; justify-content: space-between; }
        .sea-bar .sb-main { flex-direction: column; align-items: flex-start; gap: 1px; }
        .sea-bar .sb-lbl-desktop { display: none; }
        .sea-bar .sb-lbl-mobile { display: inline; font-size: 12px; color: #d7eef1; }
        .sea-bar .sb-cd { font-size: 15px; }
        .sea-bar .sb-cd .u { margin: 0 4px 0 1px; font-size: 12px; }
        header.nav { top: 54px; }   /* keep the sticky nav flush under the taller bar */
    }
    @media (prefers-reduced-motion: reduce) { .sea-bar #scSecs.flip { animation: none; } }
</style>

<div class="sea-bar" data-target="2027-03-25T09:00:00" role="region" aria-label="Countdown to the SEA exam">
    <div class="sb-main">
        <span class="sb-lbl">🔥&nbsp;<span class="sb-lbl-desktop">SEA 2027 in</span><span class="sb-lbl-mobile">Countdown to SEA 2027</span></span>
        <span class="sb-cd">
            <b id="scMonths">0</b><span class="u">mo</span><b id="scWeeks">0</b><span class="u">wk</span><b id="scDays">0</b><span class="u">d</span><b id="scHours">0</b><span class="u">h</span><b id="scMins">0</b><span class="u">m</span><b id="scSecs">0</b><span class="u">s</span>
        </span>
    </div>
    <a class="sb-cta" href="{{ route('register') }}">Start free →</a>
</div>

<script>
    (function () {
        var root = document.querySelector('.sea-bar'); if (!root) { return; }
        var target = new Date(root.getAttribute('data-target'));
        var g = function (id) { return document.getElementById(id); };
        var els = { months: g('scMonths'), weeks: g('scWeeks'), days: g('scDays'), hours: g('scHours'), mins: g('scMins'), secs: g('scSecs') };
        var DAY = 86400000, HR = 3600000, MIN = 60000;

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
            els.hours.textContent = b.hours; els.mins.textContent = b.mins;
            if (els.secs.textContent !== String(b.secs)) {
                els.secs.textContent = b.secs;
                els.secs.classList.remove('flip'); void els.secs.offsetWidth; els.secs.classList.add('flip');
            }
        }
        tick(); setInterval(tick, 1000);
    })();
</script>
