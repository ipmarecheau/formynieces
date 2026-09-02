{{-- Parent-portal demo: a looping, vector-SVG simulation of the Guardian Bridge
     parent portal, with a simulated cursor and the caption BELOW the stage — the
     parent counterpart to partials/sim-demo (the student demo in the hero). Pure
     SVG/CSS/JS, no assets. --}}
<style>
    .psim-wrap { max-width: 960px; margin: clamp(30px, 5vw, 46px) auto 0; }
    .psim-stage { position: relative; border-radius: 22px; overflow: hidden; border: 1px solid var(--line);
        background: #08152c; aspect-ratio: 1000 / 620; box-shadow: var(--shadow-lg); }
    .psim-stage svg.psim-app { position: absolute; inset: 0; width: 100%; height: 100%; display: block; }
    .psim-scene { opacity: 0; transition: opacity .55s ease; }
    .psim-scene.on { opacity: 1; }
    .psim-cursor { position: absolute; left: 50%; top: 50%; width: 4.6%; z-index: 7;
        transform: translate(-38%,-28%);
        transition: left .95s cubic-bezier(.5,.05,.25,1), top .95s cubic-bezier(.5,.05,.25,1); pointer-events: none; }
    .psim-cursor svg { width: 100%; height: auto; display: block; filter: drop-shadow(0 3px 4px rgba(0,0,0,.5)); }
    .psim-cursor.press { animation: psimPress .5s cubic-bezier(.34,1.56,.5,1); }
    @keyframes psimPress { 0%{transform:translate(-38%,-28%) scale(1)} 35%{transform:translate(-38%,-28%) scale(.55)} 100%{transform:translate(-38%,-28%) scale(1)} }
    .psim-ring, .psim-flash { position: absolute; z-index: 5; transform: translate(-50%,-50%) scale(.25); opacity: 0; pointer-events: none; }
    .psim-ring { width: 4%; aspect-ratio: 1; border-radius: 50%; }
    .psim-ring1 { border: 4px solid var(--amber); } .psim-ring2 { border: 3px solid #fff; }
    .psim-flash { width: 3.2%; aspect-ratio: 1; border-radius: 50%;
        background: radial-gradient(circle, rgba(245,181,68,.85), rgba(245,181,68,0) 70%); }
    .psim-ring1.go { animation: psimRp1 .62s ease-out; } .psim-ring2.go { animation: psimRp2 .62s ease-out .06s; } .psim-flash.go { animation: psimFl .5s ease-out; }
    @keyframes psimRp1 { 0%{opacity:.95; transform:translate(-50%,-50%) scale(.25)} 100%{opacity:0; transform:translate(-50%,-50%) scale(4.6)} }
    @keyframes psimRp2 { 0%{opacity:.85; transform:translate(-50%,-50%) scale(.2)} 100%{opacity:0; transform:translate(-50%,-50%) scale(3.2)} }
    @keyframes psimFl  { 0%{opacity:.9; transform:translate(-50%,-50%) scale(.2)} 100%{opacity:0; transform:translate(-50%,-50%) scale(2.2)} }
    .psim-capbar { display: flex; align-items: center; gap: 14px; max-width: 820px; margin: 18px auto 0; }
    .psim-capbar .n { flex: none; width: 44px; height: 44px; border-radius: 12px; display: grid; place-items: center;
        background: linear-gradient(160deg,#ffd15c,#f2941f); color: #241505; font-family: 'Fredoka', sans-serif; font-weight: 700; font-size: 18px; box-shadow: 0 6px 16px rgba(242,169,0,.28); }
    .psim-capbar .t { font-family: 'Fredoka', sans-serif; font-weight: 500; font-size: clamp(15px,2.4vw,21px); color: var(--ink); }
    .psim-newtag { margin-left: 10px; font-family: 'Nunito', sans-serif; font-size: 11px; font-weight: 800; letter-spacing: .08em;
        color: #0a5c68; background: #ffe1a3; padding: 2px 8px; border-radius: 999px; vertical-align: 2px; opacity: 0; transition: opacity .4s ease; }
    .psim-newtag.show { opacity: 1; }
    .psim-captrack { max-width: 820px; margin: 14px auto 0; height: 6px; border-radius: 999px; background: var(--line); overflow: hidden; }
    .psim-captrack i { display: block; height: 100%; width: 0; background: var(--teal); border-radius: 999px; transition: width .5s ease; }
    svg.psim-app text { font-family: 'Nunito', sans-serif; }
    svg.psim-app .fred { font-family: 'Fredoka', sans-serif; }
    @media (prefers-reduced-motion: reduce) { .psim-cursor, .psim-scene { transition: none; } }
</style>

<div class="psim-wrap">
    <div class="psim-stage" id="psimStage">
        <svg class="psim-app" viewBox="0 0 1000 620" role="img" aria-label="Animated simulation of the SmoothSeas parent portal">
            <defs>
                <clipPath id="psimRound"><rect x="0" y="0" width="1000" height="620"/></clipPath>
                <linearGradient id="psimSide" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#0f3b45"/><stop offset="1" stop-color="#0b2c34"/>
                </linearGradient>
                <linearGradient id="psimGold" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0" stop-color="#f6c65e"/><stop offset="1" stop-color="#f2941f"/>
                </linearGradient>
            </defs>
            <g clip-path="url(#psimRound)">
                <rect x="0" y="0" width="1000" height="620" fill="#faf7f0"/>
                <!-- sidebar -->
                <rect x="0" y="0" width="212" height="620" fill="url(#psimSide)"/>
                <g transform="translate(28,30)">
                    <rect x="0" y="0" width="34" height="34" rx="10" fill="#0d5560"/>
                    <text x="17" y="23" text-anchor="middle" font-size="17" fill="#bfeef1">⚓</text>
                    <text class="fred" x="46" y="16" font-size="17" font-weight="600" fill="#ffffff">SmoothSeas</text>
                    <text x="46" y="31" font-size="8.5" letter-spacing="2.4" fill="#5fb6c0" font-weight="700">GUARDIAN BRIDGE</text>
                </g>
                <text x="28" y="92" font-size="9.5" letter-spacing="2.2" fill="#4f8f99" font-weight="700">THE HONEST LAYER</text>
                <g font-size="13" fill="#cfe6ea" font-weight="600">
                    <g id="psimNavhi"><rect x="14" y="104" width="184" height="34" rx="9" fill="#0d5560"/></g>
                    <text x="34" y="126">🧭  Overview</text>
                    <text x="34" y="162">🗓  This week</text>
                    <text x="34" y="198">🧮  Pace</text>
                    <text x="34" y="234">📈  Progress</text>
                    <text x="34" y="270">🎯  Estimator</text>
                    <text x="34" y="306">🎁  Rewards &amp; controls</text>
                    <text x="34" y="342">👪  Family</text>
                    <text x="34" y="378">🔑  Children's logins</text>
                    <text x="34" y="414">⚙️  Account</text>
                </g>
                <g transform="translate(28,560)">
                    <text font-size="12" fill="#9fc7cd" font-weight="700">Demo Guardian</text>
                    <rect x="-14" y="14" width="184" height="34" rx="9" fill="none" stroke="#25636d" stroke-width="1.4"/>
                    <text x="78" y="36" text-anchor="middle" font-size="12" fill="#cfe6ea" font-weight="700">Log out</text>
                </g>

                <!-- content header (per-scene) -->
                <text class="fred" id="psimTitle" x="248" y="58" font-size="26" font-weight="600" fill="#142b3d">Overview</text>
                <text id="psimSub" x="248" y="82" font-size="13" fill="#5a6f7d">Week of 31 Aug · the four questions, answered honestly.</text>

                <!-- SCENE 1 -->
                <g class="psim-scene" id="psimSc1">
                    <g transform="translate(248,110)">
                        <rect width="172" height="96" rx="14" fill="#ffffff" stroke="#e7ddc7"/>
                        <text x="16" y="28" font-size="9.5" letter-spacing="1.4" fill="#5a6f7d" font-weight="800">READINESS</text>
                        <text class="fred" x="16" y="58" font-size="17" font-weight="600" fill="#142b3d">Mostly on pace</text>
                        <text x="16" y="78" font-size="11.5" fill="#7c8a95">a little to catch up</text>
                    </g>
                    <g transform="translate(432,110)">
                        <rect width="172" height="96" rx="14" fill="#ffffff" stroke="#e7ddc7"/>
                        <text x="16" y="28" font-size="9.5" letter-spacing="1.4" fill="#5a6f7d" font-weight="800">SEA EXAM IN</text>
                        <text class="fred" x="16" y="62" font-size="30" font-weight="600" fill="#142b3d">212<tspan font-size="14" fill="#7c8a95"> days</tspan></text>
                        <text x="16" y="82" font-size="11" fill="#7c8a95">1 Apr 2027</text>
                    </g>
                    <g transform="translate(616,110)">
                        <rect width="172" height="96" rx="14" fill="#ffffff" stroke="#e7ddc7"/>
                        <text x="16" y="28" font-size="9.5" letter-spacing="1.4" fill="#5a6f7d" font-weight="800">THIS WEEK'S TARGET</text>
                        <rect x="16" y="42" width="104" height="26" rx="7" fill="#eaf6f0"/>
                        <text x="30" y="60" font-size="12" fill="#2f9e7f" font-weight="800">In progress</text>
                    </g>
                    <g transform="translate(800,110)">
                        <rect width="172" height="96" rx="14" fill="url(#psimSide)"/>
                        <text x="16" y="28" font-size="9.5" letter-spacing="1.4" fill="#8fd3da" font-weight="800">OVERALL MASTERY</text>
                        <text class="fred" x="16" y="66" font-size="32" font-weight="600" fill="#ffffff">4<tspan font-size="15" fill="#bfeef1">%</tspan></text>
                        <text x="16" y="84" font-size="10.5" fill="#9fd6dd">4 of 90 modules</text>
                    </g>
                    <g transform="translate(248,228)">
                        <rect width="724" height="96" rx="14" fill="#fdf4dd" stroke="#f0dd9f"/>
                        <text x="20" y="30" font-size="9.5" letter-spacing="1.4" fill="#a9791b" font-weight="800">WHERE DEMO STUDENT STANDS</text>
                        <text class="fred" x="20" y="58" font-size="19" font-weight="600" fill="#7a5a12">Mostly on pace, a little to catch up</text>
                        <text x="20" y="80" font-size="12.5" fill="#8a7433">1 module behind across all subjects — well within reach this term.</text>
                    </g>
                    <g transform="translate(248,344)">
                        <rect width="724" height="230" rx="14" fill="#ffffff" stroke="#e7ddc7"/>
                        <text x="22" y="34" font-size="10" letter-spacing="1.2" fill="#1f7a86" font-weight="800">EXAM AGENT · STRENGTHS &amp; WHAT TO WORK ON</text>
                        <text x="22" y="66" font-size="12.5" fill="#43566a">Doing well in Math — perfectly on track, no modules behind. Two ELA</text>
                        <text x="22" y="86" font-size="12.5" fill="#43566a">modules behind puts them at slight risk. To catch up without overwhelm,</text>
                        <text x="22" y="106" font-size="12.5" fill="#43566a">complete just one ELA module this week while keeping the math routine.</text>
                        <rect x="22" y="126" width="680" height="1" fill="#eee3ce"/>
                        <text x="22" y="152" font-size="10" letter-spacing="1.2" fill="#1f7a86" font-weight="800">RECOMMENDATION</text>
                        <text x="22" y="178" font-size="12.5" fill="#43566a">Start with “Reading Comprehension: Identifying Main Idea”. 30 weeks to go.</text>
                        <text x="22" y="212" font-size="11.5" fill="#7c8a95">★ Math — 7 ahead   ★ ELA — 4 ahead   ★ Writing — essay avg 8.3/10</text>
                    </g>
                </g>

                <!-- SCENE 2 -->
                <g class="psim-scene" id="psimSc2">
                    <g transform="translate(248,120)">
                        <rect width="724" height="150" rx="16" fill="#ffffff" stroke="#e7ddc7"/>
                        <circle cx="60" cy="75" r="34" fill="#eaf6f0"/>
                        <text x="60" y="86" text-anchor="middle" font-size="34">🎯</text>
                        <text x="118" y="52" font-size="10" letter-spacing="1.2" fill="#1f7a86" font-weight="800">YOUR ONE NEXT STEP THIS WEEK</text>
                        <text class="fred" x="118" y="86" font-size="22" font-weight="600" fill="#142b3d">One ELA module — Main Idea</text>
                        <text x="118" y="114" font-size="12.5" fill="#6a7b88">Keep the strong math routine; this single focus closes the gap.</text>
                    </g>
                    <g transform="translate(248,290)">
                        <rect width="352" height="150" rx="16" fill="#ffffff" stroke="#e7ddc7"/>
                        <text x="22" y="34" font-size="10" letter-spacing="1.2" fill="#5a6f7d" font-weight="800">WHY IT'S HONEST</text>
                        <text x="22" y="66" font-size="12.5" fill="#43566a">No streaks or spin — just where</text>
                        <text x="22" y="86" font-size="12.5" fill="#43566a">they truly stand, recalculated</text>
                        <text x="22" y="106" font-size="12.5" fill="#43566a">every week.</text>
                    </g>
                    <g transform="translate(620,290)">
                        <rect width="352" height="150" rx="16" fill="url(#psimSide)"/>
                        <text x="22" y="34" font-size="10" letter-spacing="1.2" fill="#8fd3da" font-weight="800">SMOOTH'S TAKE</text>
                        <text x="22" y="66" font-size="12.5" fill="#dceff1" fill-opacity="0.92">“A steady week here and the</text>
                        <text x="22" y="86" font-size="12.5" fill="#dceff1" fill-opacity="0.92">ELA gap is closed — no drama,</text>
                        <text x="22" y="106" font-size="12.5" fill="#dceff1" fill-opacity="0.92">no cramming.”</text>
                    </g>
                </g>

                <!-- SCENE 3 -->
                <g class="psim-scene" id="psimSc3">
                    <g transform="translate(248,116)">
                        <rect width="724" height="216" rx="16" fill="#ffffff" stroke="#e7ddc7"/>
                        <text class="fred" x="22" y="40" font-size="18" font-weight="600" fill="#142b3d">Mathematics</text>
                        <text x="702" y="40" text-anchor="end" font-size="12.5" fill="#5a6f7d" font-weight="700">10 of 51 mastered</text>
                        <rect x="22" y="54" width="680" height="9" rx="5" fill="#eee3ce"/>
                        <rect x="22" y="54" width="134" height="9" rx="5" fill="#1f7a86"/>
                        <text x="22" y="92" font-size="10.5" fill="#2f9e7f" font-weight="800">MASTERED 10</text>
                        <text x="22" y="118" font-size="12.5" fill="#43566a">Number Concepts: Place Value up to One Million</text>
                        <text x="22" y="140" font-size="12.5" fill="#43566a">Whole Number Operations: Multiplication &amp; Division</text>
                        <text x="22" y="162" font-size="12.5" fill="#43566a">Number Patterns: Rules and Missing Elements</text>
                        <text x="22" y="192" font-size="11.5" fill="#1f7a86" font-weight="700">▸ Upcoming (41) — Show all</text>
                    </g>
                    <g transform="translate(248,348)">
                        <rect width="724" height="226" rx="16" fill="#ffffff" stroke="#e7ddc7"/>
                        <text class="fred" x="22" y="40" font-size="18" font-weight="600" fill="#142b3d">ELA</text>
                        <text x="702" y="40" text-anchor="end" font-size="12.5" fill="#5a6f7d" font-weight="700">5 of 39 mastered</text>
                        <rect x="22" y="54" width="680" height="9" rx="5" fill="#eee3ce"/>
                        <rect x="22" y="54" width="88" height="9" rx="5" fill="#1f7a86"/>
                        <text x="22" y="92" font-size="10.5" fill="#c07a1b" font-weight="800">WORKING ON 1</text>
                        <text x="22" y="116" font-size="12.5" fill="#43566a">Spelling: ie/ei Words, Silent Letters and Homophones</text>
                        <text x="22" y="150" font-size="10.5" fill="#2f9e7f" font-weight="800">MASTERED 5</text>
                        <text x="22" y="174" font-size="12.5" fill="#43566a">Spelling: Prefixes, Suffixes and Root Words</text>
                        <text x="22" y="196" font-size="12.5" fill="#43566a">Spelling: Synonyms, Antonyms &amp; Multiple-meaning Words</text>
                    </g>
                </g>

                <!-- SCENE 4 -->
                <g class="psim-scene" id="psimSc4">
                    <g transform="translate(248,130)">
                        <rect width="352" height="300" rx="18" fill="#ffffff" stroke="#e7ddc7"/>
                        <text x="176" y="44" text-anchor="middle" font-size="10" letter-spacing="1.2" fill="#5a6f7d" font-weight="800">PROJECTED COMPOSITE</text>
                        <circle cx="176" cy="160" r="78" fill="none" stroke="#eee3ce" stroke-width="16"/>
                        <circle cx="176" cy="160" r="78" fill="none" stroke="#1f7a86" stroke-width="16" stroke-linecap="round"
                                stroke-dasharray="490" stroke-dashoffset="83" transform="rotate(-90 176 160)"/>
                        <text class="fred" x="176" y="168" text-anchor="middle" font-size="46" font-weight="600" fill="#142b3d">83%</text>
                        <rect x="106" y="248" width="140" height="30" rx="8" fill="#eaf6f0"/>
                        <text x="176" y="268" text-anchor="middle" font-size="12.5" fill="#2f9e7f" font-weight="800">High confidence</text>
                    </g>
                    <g transform="translate(620,130)">
                        <rect width="352" height="300" rx="18" fill="#ffffff" stroke="#e7ddc7"/>
                        <text x="22" y="40" font-size="10" letter-spacing="1.2" fill="#5a6f7d" font-weight="800">INDICATIVE SEA TIER</text>
                        <text class="fred" x="22" y="76" font-size="22" font-weight="600" fill="#142b3d">First-choice range</text>
                        <text x="22" y="104" font-size="12.5" fill="#6a7b88">Weighted 50 / 30 / 20 across the papers.</text>
                        <g font-size="12.5" fill="#43566a">
                            <text x="22" y="150">Maths paper</text><text x="330" y="150" text-anchor="end" font-weight="800" fill="#142b3d">88%</text>
                            <text x="22" y="182">Language paper</text><text x="330" y="182" text-anchor="end" font-weight="800" fill="#142b3d">79%</text>
                            <text x="22" y="214">Creative writing</text><text x="330" y="214" text-anchor="end" font-weight="800" fill="#142b3d">80%</text>
                        </g>
                        <rect x="22" y="238" width="308" height="1" fill="#eee3ce"/>
                        <text x="22" y="266" font-size="11.5" fill="#7c8a95">An honest projection — not a promise.</text>
                    </g>
                </g>

                <!-- SCENE 5 -->
                <g class="psim-scene" id="psimSc5">
                    <g transform="translate(248,120)">
                        <rect width="724" height="200" rx="16" fill="#ffffff" stroke="#e7ddc7"/>
                        <g transform="translate(230,44)">
                            <rect x="0" y="0" width="130" height="52" rx="12" fill="#ffffff" stroke="#1f7a86" stroke-width="2"/>
                            <text x="65" y="20" text-anchor="middle" font-size="9" letter-spacing="1.4" fill="#1f7a86" font-weight="800">YOU</text>
                            <text class="fred" x="65" y="40" text-anchor="middle" font-size="14" font-weight="600" fill="#142b3d">Demo Guardian</text>
                            <text x="150" y="30" font-size="16" fill="#8a97a1">&amp;</text>
                            <rect x="170" y="0" width="130" height="52" rx="12" fill="#f6f2e8" stroke="#d9cdb4" stroke-dasharray="5 4"/>
                            <text x="235" y="20" text-anchor="middle" font-size="9" letter-spacing="1.4" fill="#9a8b6d" font-weight="800">OTHER PARENT</text>
                            <text class="fred" x="235" y="40" text-anchor="middle" font-size="13" font-weight="600" fill="#9a8b6d">Not added yet</text>
                            <line x1="65" y1="52" x2="65" y2="92" stroke="#d9cdb4" stroke-width="2"/>
                            <rect x="0" y="92" width="130" height="52" rx="12" fill="#ffffff" stroke="#f2a41c" stroke-width="2"/>
                            <text x="65" y="112" text-anchor="middle" font-size="9" letter-spacing="1.4" fill="#c07a1b" font-weight="800">CHILD</text>
                            <text class="fred" x="65" y="132" text-anchor="middle" font-size="14" font-weight="600" fill="#142b3d">Demo Student</text>
                        </g>
                    </g>
                    <g transform="translate(248,336)">
                        <rect width="724" height="238" rx="16" fill="#ffffff" stroke="#e7ddc7"/>
                        <text class="fred" x="22" y="40" font-size="17" font-weight="600" fill="#142b3d">The other parent</text>
                        <text x="22" y="64" font-size="12" fill="#6a7b88">Add one other parent or guardian — they'll get an email to join.</text>
                        <text x="22" y="100" font-size="9.5" letter-spacing="1.2" fill="#5a6f7d" font-weight="800">THEIR NAME</text>
                        <rect x="22" y="110" width="330" height="40" rx="9" fill="#faf7f0" stroke="#e2d9c4"/>
                        <text x="38" y="135" font-size="12.5" fill="#142b3d">Marcus Thomas</text>
                        <text x="372" y="100" font-size="9.5" letter-spacing="1.2" fill="#5a6f7d" font-weight="800">THEIR EMAIL</text>
                        <rect x="372" y="110" width="330" height="40" rx="9" fill="#faf7f0" stroke="#e2d9c4"/>
                        <text x="388" y="135" font-size="12.5" fill="#142b3d">marcus@example.com</text>
                        <rect x="22" y="176" width="150" height="42" rx="10" fill="url(#psimGold)"/>
                        <text x="97" y="202" text-anchor="middle" class="fred" font-size="14" font-weight="600" fill="#241505">Send invitation</text>
                    </g>
                </g>

                <!-- SCENE 6 -->
                <g class="psim-scene" id="psimSc6">
                    <g transform="translate(248,120)">
                        <rect width="724" height="196" rx="16" fill="#ffffff" stroke="#e7ddc7"/>
                        <text class="fred" x="22" y="40" font-size="17" font-weight="600" fill="#142b3d">Demo Student</text>
                        <text x="22" y="70" font-size="9.5" letter-spacing="1.2" fill="#5a6f7d" font-weight="800">LOGIN ID (EMAIL)</text>
                        <text class="fred" x="22" y="98" font-size="19" font-weight="600" fill="#142b3d">demo-student@smoothseas.test</text>
                        <rect x="22" y="120" width="176" height="44" rx="10" fill="#f6f2e8" stroke="#d9cdb4"/>
                        <text x="110" y="147" text-anchor="middle" font-size="13" fill="#0f3b45" font-weight="700">👁  Reveal password</text>
                        <rect x="212" y="120" width="176" height="44" rx="10" fill="url(#psimGold)"/>
                        <text x="300" y="147" text-anchor="middle" font-size="13" fill="#241505" font-weight="700">🔄  Reset password</text>
                        <text x="410" y="147" font-size="11.5" fill="#8a97a1">You always hold the keys.</text>
                    </g>
                    <g transform="translate(248,332)">
                        <rect width="724" height="242" rx="16" fill="#ffffff" stroke="#e7ddc7"/>
                        <text x="22" y="38" font-size="10" letter-spacing="1.2" fill="#1f7a86" font-weight="800">CONTROLS &amp; REWARDS · never shown to the child</text>
                        <g class="fred" font-size="13" font-weight="600" fill="#241505">
                            <rect x="22" y="56" width="150" height="40" rx="10" fill="url(#psimGold)"/><text x="97" y="81" text-anchor="middle">Grant Shore Leave</text>
                            <rect x="184" y="56" width="130" height="40" rx="10" fill="url(#psimGold)"/><text x="249" y="81" text-anchor="middle">Grant Anchor</text>
                            <rect x="326" y="56" width="140" height="40" rx="10" fill="url(#psimGold)"/><text x="396" y="81" text-anchor="middle">Grant Tailwind</text>
                        </g>
                        <g font-size="13" font-weight="700" fill="#0f3b45">
                            <rect x="22" y="108" width="150" height="40" rx="10" fill="#ffffff" stroke="#cdd9db"/><text x="97" y="133" text-anchor="middle" class="fred" font-weight="600">Pause journey</text>
                            <rect x="184" y="108" width="160" height="40" rx="10" fill="#ffffff" stroke="#cdd9db"/><text x="264" y="133" text-anchor="middle" class="fred" font-weight="600">Resume journey</text>
                            <rect x="356" y="108" width="200" height="40" rx="10" fill="#ffffff" stroke="#1f7a86"/><text x="456" y="133" text-anchor="middle" class="fred" font-weight="600" fill="#1f7a86">Request diagnostic retake</text>
                        </g>
                        <text x="22" y="188" font-size="10" letter-spacing="1.2" fill="#1f7a86" font-weight="800">FROM SCHOOL · JOURNAL</text>
                        <text x="22" y="212" font-size="12" fill="#43566a">11 Aug — Comprehension, Quiz: 78%    ·    22 Aug — Place Value, Class Test: 82%</text>
                    </g>
                </g>
            </g>
        </svg>

        <div class="psim-flash" id="psimFlash"></div>
        <div class="psim-ring psim-ring1" id="psimRing1"></div>
        <div class="psim-ring psim-ring2" id="psimRing2"></div>
        <div class="psim-cursor" id="psimCursor">
            <svg viewBox="0 0 24 24" fill="none"><path d="M4 2 L4 20 L9 15 L12.5 22 L15 21 L11.5 14 L18 14 Z" fill="#fff" stroke="#12222e" stroke-width="1.4" stroke-linejoin="round"/></svg>
        </div>
    </div>

    <div class="psim-capbar"><span class="n" id="psimCapN">1</span><span class="t" id="psimCapT">Four honest answers — where they really stand<span class="psim-newtag" id="psimCapTag">NEW</span></span></div>
    <div class="psim-captrack"><i id="psimProg"></i></div>
</div>

<script>
    (function () {
        var stage = document.getElementById('psimStage');
        if (!stage) { return; }
        var cursor = document.getElementById('psimCursor');
        var ring1 = document.getElementById('psimRing1'), ring2 = document.getElementById('psimRing2'), flash = document.getElementById('psimFlash');
        var capN = document.getElementById('psimCapN'), capT = document.getElementById('psimCapT'), capTag = document.getElementById('psimCapTag'), prog = document.getElementById('psimProg');
        var ctitle = document.getElementById('psimTitle'), csub = document.getElementById('psimSub');
        var navhi = document.getElementById('psimNavhi');

        var scenes = [
            { id:'psimSc1', cap:'Four honest answers — where they really stand', title:'Overview', sub:'Week of 31 Aug · the four questions, answered honestly.', navY:104, cx:56, cy:26, tag:false },
            { id:'psimSc2', cap:'One clear next step — not a wall of data',        title:'Overview', sub:'The single thing to focus on next.',                     navY:104, cx:42, cy:34, tag:false },
            { id:'psimSc3', cap:'Every topic — mastered, working on, upcoming',    title:'Progress drill-down', sub:'What needs attention first — the whole syllabus.', navY:212, cx:38, cy:48, tag:false },
            { id:'psimSc4', cap:'A projected SEA placement, honestly signalled',   title:'Estimator', sub:'An indicative tier, with a confidence signal.',        navY:248, cx:44, cy:42, tag:false },
            { id:'psimSc5', cap:'The whole family — invite the other parent',      title:'Family', sub:"Your children's details, and the other parent.",        navY:320, cx:38, cy:74, tag:true },
            { id:'psimSc6', cap:"Their logins in hand — and you're in control",    title:"Children's logins", sub:'Reveal or reset a password; grant, pause, retake.', navY:356, cx:50, cy:30, tag:true }
        ];

        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        function px(pct, axis) { var r = stage.getBoundingClientRect(); return (axis === 'x' ? r.width : r.height) * pct / 100; }
        function place(cx, cy) { cursor.style.left = px(cx,'x') + 'px'; cursor.style.top = px(cy,'y') + 'px'; }
        function retrigger(el, cx, cy) { el.style.left = px(cx,'x') + 'px'; el.style.top = px(cy,'y') + 'px'; el.classList.remove('go'); void el.offsetWidth; el.classList.add('go'); }
        function clickFx(cx, cy) { cursor.classList.remove('press'); void cursor.offsetWidth; cursor.classList.add('press'); retrigger(flash,cx,cy); retrigger(ring1,cx,cy); retrigger(ring2,cx,cy); }

        if (reduce) { document.getElementById('psimSc1').classList.add('on'); return; }

        var i = -1;
        function step() {
            i = (i + 1) % scenes.length;
            var s = scenes[i];
            scenes.forEach(function (sc) { document.getElementById(sc.id).classList.toggle('on', sc.id === s.id); });
            ctitle.textContent = s.title; csub.textContent = s.sub;
            navhi.querySelector('rect').setAttribute('y', s.navY);
            capN.textContent = (i + 1);
            capT.childNodes[0].nodeValue = s.cap + ' ';
            capTag.classList.toggle('show', !!s.tag);
            prog.style.width = Math.round(((i + 1) / scenes.length) * 100) + '%';
            setTimeout(function () { place(s.cx, s.cy); }, 600);
            setTimeout(function () { clickFx(s.cx, s.cy); }, 1750);
            setTimeout(step, 4700);
        }
        setTimeout(step, 500);
    })();
</script>
