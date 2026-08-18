<div class="co-root" wire:key="captains-orders">
    {{-- Rustic voyage sidebar. Emoji stand in for art Isaac will supply:
         🐢 Smooth · 📜 scroll · ⚓/🌬️/🛟/🏝️ reward tokens. Swap for real assets later. --}}
    <style>
        .co-root { font-family: 'Nunito', sans-serif; }

        @keyframes co-unroll { from { transform: scaleY(.22); opacity: 0; } to { transform: scaleY(1); opacity: 1; } }
        @keyframes co-rail-in { from { opacity: 0; transform: translateX(-14px); } to { opacity: 1; transform: none; } }

        /* Collapsed rail — a rolled scroll pinned to the mast */
        .co-rail {
            position: fixed; left: 0; top: 84px; z-index: 40;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px;
            width: 72px; min-height: 240px; padding: 18px 8px; cursor: pointer;
            background: linear-gradient(180deg, #6b4a2b, #4a3119);
            border: 2px solid #3a2712; border-left: none;
            border-radius: 0 16px 16px 0;
            box-shadow: 3px 4px 12px rgba(0,0,0,0.45);
            color: #f0e0bd;
            animation: co-rail-in .26s ease-out;
        }
        .co-rail:hover { background: linear-gradient(180deg, #7a5730, #533722); }
        .co-rail-ico { font-size: 1.7rem; }
        .co-rail-label {
            font-family: 'Fredoka One', cursive; font-size: 0.82rem; line-height: 1.05;
            text-align: center;
        }
        .co-rail-arrow { font-size: 1.15rem; color: #f6b71e; line-height: 1; }

        /* Expanded parchment panel in a wood frame */
        .co-panel {
            position: fixed; left: 0; top: 88px; z-index: 40;
            width: min(320px, 86vw);
        }
        /* Arrow marker on the side of the scroll — collapse toward the edge */
        .co-edge-toggle {
            position: absolute; top: 60px; right: -18px; z-index: 41;
            width: 22px; height: 60px; cursor: pointer;
            display: grid; place-items: center;
            background: linear-gradient(180deg, #6b4a2b, #4a3119);
            color: #f6b71e; border: 2px solid #3a2712; border-left: none;
            border-radius: 0 10px 10px 0; font-size: 1rem; font-weight: 900;
            box-shadow: 2px 2px 6px rgba(0,0,0,0.35);
        }
        .co-edge-toggle:hover { background: linear-gradient(180deg, #7a5730, #533722); }
        .co-frame {
            max-height: calc(100vh - 120px); overflow-y: auto;
            transform-origin: top center;
            animation: co-unroll .34s cubic-bezier(.2,.85,.25,1);
            background:
                repeating-linear-gradient(90deg, transparent 0 6px, rgba(90,61,33,0.06) 6px 7px),
                linear-gradient(160deg, #f4e8c8 0%, #ecdcb0 55%, #e3cf9c 100%);
            border: 6px solid #5a3d21;
            border-radius: 4px 14px 14px 4px;
            box-shadow: 4px 6px 18px rgba(0,0,0,0.45), inset 0 0 34px rgba(120,84,40,0.28);
            color: #3d2b16;
            padding: 0 0 14px;
        }
        .co-frame::before {
            content: ""; display: block; height: 5px;
            background: repeating-linear-gradient(45deg, #b98a4b 0 6px, #8a6531 6px 12px);
        }

        @media (prefers-reduced-motion: reduce) {
            .co-frame, .co-rail { animation: none; }
        }

        .co-head { display: flex; align-items: center; gap: 10px; padding: 12px 12px 8px; }
        .co-crest {
            width: 40px; height: 40px; flex: none;
            display: grid; place-items: center; font-size: 1.4rem;
            background: radial-gradient(circle at 35% 30%, #fbe9c0, #c9791f);
            border: 2px solid #7a4a1a; border-radius: 50%;
            box-shadow: inset 0 -2px 6px rgba(0,0,0,0.25);
        }
        .co-title-main { font-family: 'Fredoka One', cursive; font-size: 1.15rem; color: #4a3119; line-height: 1; }
        .co-title-sub { font-size: 0.72rem; font-weight: 800; color: #8a6531; text-transform: uppercase; letter-spacing: 1px; }

        .co-tabs { display: flex; gap: 3px; padding: 4px 10px 10px; }
        .co-tab {
            flex: 1; cursor: pointer; padding: 6px 3px;
            font-family: 'Fredoka One', cursive; font-size: 0.72rem;
            color: #7a5a2e; background: rgba(90,61,33,0.08);
            border: 1.5px solid #b98a4b; border-radius: 7px;
        }
        .co-tab.is-on { color: #f4e8c8; background: linear-gradient(135deg, #6b4a2b, #8a6531); }

        .co-body { padding: 0 14px; }
        .co-lead { font-size: 0.86rem; font-weight: 700; margin: 2px 0 10px; }

        /* Weekly mission block */
        .co-week { margin: 2px 0 12px; padding: 10px 11px; border-radius: 9px; background: rgba(201,121,31,0.12); border: 1.5px solid #c9a35a; }
        .co-week-title { font-family: 'Fredoka One', cursive; font-size: 0.9rem; color: #7a4a1a; }
        .co-week-prog { font-size: 0.8rem; font-weight: 800; color: #6b4a2b; margin-top: 2px; }
        .co-week-bar { margin-top: 7px; height: 9px; border-radius: 999px; background: rgba(90,61,33,0.18); overflow: hidden; border: 1px solid #b98a4b; }
        .co-week-bar span { display: block; height: 100%; background: linear-gradient(90deg, #2f7d5b, #57b98a); }

        /* Checklist */
        .co-checklist { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 7px; }
        .co-duty { display: flex; align-items: center; gap: 9px; padding: 8px 10px; border-radius: 9px; background: rgba(255,255,255,0.4); border: 1.5px dashed #b98a4b; font-size: 0.86rem; font-weight: 700; }
        .co-duty.is-done { background: rgba(52,125,91,0.16); border-style: solid; border-color: #2f7d5b; color: #245c43; }
        .co-check { width: 20px; height: 20px; flex: none; border-radius: 50%; display: grid; place-items: center; font-size: 0.8rem; font-weight: 900; border: 2px solid #b98a4b; color: #2f7d5b; }
        .co-duty.is-done .co-check { background: #2f7d5b; color: #fff; border-color: #2f7d5b; }
        .co-duty-label { flex: 1; }
        .co-duty-note { font-size: 0.68rem; font-weight: 800; color: #8a6531; text-transform: uppercase; }
        .co-duty-do { cursor: pointer; border: none; border-radius: 6px; padding: 3px 8px; font-size: 0.68rem; font-weight: 800; color: #f4e8c8; background: #6b4a2b; }
        .co-soon { font-size: 0.6rem; font-weight: 800; color: #9e3b23; text-transform: uppercase; }

        .co-gate { margin: 12px 0 0; padding: 9px 10px; font-size: 0.8rem; font-weight: 700; background: rgba(201,121,31,0.16); border-left: 4px solid #c9791f; border-radius: 6px; color: #7a4a1a; }
        .co-alldone { margin: 12px 0 0; font-family: 'Fredoka One', cursive; color: #2f7d5b; text-align: center; }
        .co-review { margin: 12px 0 0; width: 100%; cursor: pointer; background: none; border: 1.5px solid #b98a4b; border-radius: 8px; padding: 7px; font-weight: 800; color: #7a5a2e; }

        /* Rest day */
        .co-rest { text-align: center; padding: 14px 6px; }
        .co-rest-ico { font-size: 2.2rem; }
        .co-rest-txt { font-size: 0.9rem; font-weight: 700; margin: 8px 0 0; }

        /* Journal */
        .co-streak-hero { text-align: center; padding: 8px 0 12px; }
        .co-streak-num { font-family: 'Fredoka One', cursive; font-size: 2.6rem; color: #c9791f; line-height: 1; }
        .co-streak-cap { font-size: 0.78rem; font-weight: 800; color: #7a5a2e; text-transform: uppercase; letter-spacing: 1px; }
        .co-substreaks { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
        .co-substreaks li { display: flex; justify-content: space-between; align-items: center; padding: 6px 9px; border-radius: 7px; background: rgba(255,255,255,0.4); border: 1px solid #d9c396; font-size: 0.8rem; font-weight: 700; }
        .co-sub-num { font-family: 'Fredoka One', cursive; color: #8a6531; }
        .co-miles { display: flex; gap: 6px; margin-top: 14px; }
        .co-mile { flex: 1; text-align: center; padding: 7px 4px; border-radius: 8px; border: 1.5px dashed #b98a4b; font-weight: 800; color: #8a6531; font-size: 0.66rem; }
        .co-mile.is-hit { border-style: solid; background: rgba(201,121,31,0.16); color: #7a4a1a; }
        .co-mile-n { font-family: 'Fredoka One', cursive; font-size: 1.05rem; display: block; }

        /* Locker */
        .co-reward { position: relative; display: flex; align-items: center; gap: 9px; margin-bottom: 7px; padding: 8px 10px; border-radius: 9px; background: rgba(255,255,255,0.45); border: 1.5px solid #b98a4b; cursor: help; }
        .co-reward:hover, .co-reward:focus-within { border-color: #c9791f; box-shadow: 0 0 0 2px rgba(201,121,31,0.25); }
        .co-reward-ico { font-size: 1.35rem; flex: none; }
        .co-reward-name { font-size: 0.82rem; font-weight: 800; color: #4a3119; }
        .co-reward-count { color: #9e3b23; }
        .co-reward-blurb { font-size: 0.7rem; color: #7a5a2e; }
        .co-reward-earn { font-size: 0.66rem; color: #9e6a2e; font-weight: 700; margin-top: 3px; }
        .co-locker-empty { font-size: 0.74rem; color: #6b4a2b; font-weight: 700; line-height: 1.45; margin-bottom: 10px; }
        .co-reward-body { flex: 1; }
        .co-reward-use { cursor: pointer; border: none; border-radius: 6px; padding: 4px 10px; font-size: 0.72rem; font-weight: 800; color: #f4e8c8; background: linear-gradient(135deg, #6b4a2b, #8a6531); }
        .co-tip {
            position: absolute; left: 6px; right: 6px; top: calc(100% + 4px); z-index: 6; display: none;
            padding: 9px 11px; border-radius: 9px; background: #2c1d0c; color: #f4e8c8;
            font-size: 0.72rem; line-height: 1.35; font-weight: 600;
            box-shadow: 0 8px 18px rgba(0,0,0,0.45); border: 1px solid #b98a4b;
        }
        .co-reward:hover .co-tip, .co-reward:focus-within .co-tip { display: block; }
        /* The bottom rewards open their explanation upward so the scroll never clips it. */
        .co-reward:nth-last-child(1) .co-tip,
        .co-reward:nth-last-child(2) .co-tip { top: auto; bottom: calc(100% + 4px); }

        /* Logs */
        .co-logs { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 6px; }
        .co-logs li { display: flex; align-items: center; justify-content: space-between; padding: 7px 10px; border-radius: 7px; background: rgba(255,255,255,0.4); border: 1px solid #d9c396; font-size: 0.8rem; font-weight: 700; }
        .co-log-date { color: #4a3119; }
        .co-log-badge { font-size: 0.66rem; font-weight: 800; padding: 2px 8px; border-radius: 999px; }
        .co-log-done { background: rgba(52,125,91,0.2); color: #245c43; }
        .co-log-rest { background: rgba(90,61,33,0.14); color: #7a5a2e; }
        .co-log-open { background: rgba(158,59,35,0.14); color: #9e3b23; }
        .co-empty { font-size: 0.82rem; color: #7a5a2e; text-align: center; padding: 14px 4px; }

        @media (max-width: 640px) {
            /* CO-12: a bottom sheet so the sea stays in view above the brief, never a full-screen cover. */
            .co-panel { top: auto; bottom: 0; left: 0; width: 100vw; }
            .co-frame { max-height: 56vh; border-radius: 14px 14px 0 0; border-left: none; border-right: none; }
            .co-edge-toggle { top: 8px; right: 8px; border-radius: 8px; border-left: 2px solid #3a2712; }
            .co-rail { top: auto; bottom: 0; min-height: 0; width: auto; flex-direction: row; gap: 8px; padding: 8px 14px; border-radius: 14px 14px 0 0; }
        }
    </style>

    @if ($collapsed)
        <button class="co-rail" wire:click="toggle" title="Open Captain's Orders" aria-label="Open Captain's Orders">
            <span class="co-rail-ico">📜</span>
            <span class="co-rail-label">Captain's<br>Orders</span>
            <span class="co-rail-arrow">▸</span>
        </button>
    @else
        <aside class="co-panel" data-co12="sheet">
            <div class="co-frame">
                <header class="co-head">
                    <div class="co-crest">🐢</div>
                    <div>
                        <div class="co-title-main">Captain's Orders</div>
                        <div class="co-title-sub">{{ $isEvening ? 'Evening watch' : 'Morning muster' }}</div>
                    </div>
                </header>

                <nav class="co-tabs">
                    <button class="co-tab {{ $tab === 'orders' ? 'is-on' : '' }}" wire:click="showTab('orders')">Orders</button>
                    <button class="co-tab {{ $tab === 'locker' ? 'is-on' : '' }}" wire:click="showTab('locker')">Locker</button>
                    <button class="co-tab {{ $tab === 'journal' ? 'is-on' : '' }}" wire:click="showTab('journal')">Journal</button>
                    <button class="co-tab {{ $tab === 'logs' ? 'is-on' : '' }}" wire:click="showTab('logs')">Logs</button>
                </nav>

                @if ($tab === 'orders')
                    <div class="co-body">
                        @if ($weeklyGoal > 0)
                            <div class="co-week">
                                <div class="co-week-title">⚓ This week's mission</div>
                                <div class="co-week-prog">{{ $weeklyDone }} of {{ $weeklyGoal }} islands conquered</div>
                                <div class="co-week-bar"><span style="width: {{ $weeklyPct }}%"></span></div>
                            </div>
                        @endif

                        @if ($isRestDay)
                            <div class="co-rest">
                                <div class="co-rest-ico">⚓</div>
                                <p class="co-rest-txt">Shore leave, first mate! The seas are calm — rest and enjoy your weekend. Your streak sails on.</p>
                            </div>
                        @else
                            <p class="co-lead">
                                {{ $isEvening ? "Evening watch — here's what's still on your orders." : "Today's orders, Captain. Clear them to keep the Voyage sailing." }}
                            </p>
                            <ul class="co-checklist">
                                @foreach ($duties as $duty)
                                    <li class="co-duty {{ $duty['done'] ? 'is-done' : '' }}">
                                        <span class="co-check">{{ $duty['done'] ? '✓' : '' }}</span>
                                        <span class="co-duty-label">{{ $duty['label'] }}</span>
                                        @if (! $duty['done'])
                                            @if ($duty['key'] === 'morning_tide')
                                                <a class="co-duty-do" style="text-decoration:none;" href="{{ route('student.morning-tide') }}">start</a>
                                            @elseif ($duty['key'] === 'map')
                                                <span class="co-duty-note">at sea</span>
                                            @elseif ($duty['placeholder'])
                                                <button class="co-duty-do" wire:click="completeThread('{{ $duty['key'] }}')">mark done</button>
                                                <span class="co-soon">soon</span>
                                            @endif
                                        @endif
                                    </li>
                                @endforeach
                            </ul>

                            @if ($writingDay && ! $writingDone)
                                <p class="co-gate">✍️ Finish today's writing to open the next island on the map.</p>
                            @endif

                            @if ($allDone)
                                <p class="co-alldone">All orders cleared — a fine day's sailing! 🌊</p>
                            @elseif ($isEvening)
                                <button class="co-review" wire:click="showTab('logs')">Look back on today ›</button>
                            @endif
                        @endif
                    </div>
                @elseif ($tab === 'locker')
                    <div class="co-body">
                        @php($lockerEmpty = collect($rewards)->every(fn ($r) => $r['held'] === 0))
                        @if ($lockerEmpty)
                            <p class="co-locker-empty">Your Locker is empty for now — these are rewards to sail toward. Get ahead and reach milestones to earn them! ⛵</p>
                        @endif
                        @foreach ($rewards as $r)
                            <div class="co-reward" tabindex="0">
                                <span class="co-reward-ico">{{ ['shore_leave' => '🏝️', 'anchor' => '⚓', 'tailwind' => '🌬️', 'lifebuoy' => '🛟'][$r['type']] }}</span>
                                <div class="co-reward-body">
                                    <div class="co-reward-name">{{ $r['label'] }} <span class="co-reward-count">×{{ $r['held'] }}</span></div>
                                    <div class="co-reward-blurb">{{ $r['blurb'] }}</div>
                                    @if ($r['held'] === 0)
                                        <div class="co-reward-earn">How to earn: {{ $r['earn'] }}</div>
                                    @endif
                                </div>
                                @if ($r['held'] > 0)
                                    <button class="co-reward-use" wire:click="useReward('{{ $r['type'] }}')">Use</button>
                                @endif
                                <div class="co-tip">{{ $r['long'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @elseif ($tab === 'journal')
                    <div class="co-body">
                        <div class="co-streak-hero">
                            <div class="co-streak-num">{{ $voyageStreak }}</div>
                            <div class="co-streak-cap">day Voyage streak</div>
                        </div>
                        <ul class="co-substreaks">
                            @foreach ($subStreaks as $s)
                                <li><span class="co-sub-label">{{ $s['label'] }}</span><span class="co-sub-num">{{ $s['count'] }}</span></li>
                            @endforeach
                        </ul>
                        <div class="co-miles">
                            @foreach ($milestones as $m)
                                <div class="co-mile {{ $m['reached'] ? 'is-hit' : '' }}">
                                    <span class="co-mile-n">{{ $m['days'] }}</span>days
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="co-body">
                        @if ($logs->isEmpty())
                            <p class="co-empty">Your voyage log begins today. Sail on! ⛵</p>
                        @else
                            <ul class="co-logs">
                                @foreach ($logs as $log)
                                    <li>
                                        <span class="co-log-date">{{ $log['date'] }}</span>
                                        @if ($log['rest'])
                                            <span class="co-log-badge co-log-rest">shore leave</span>
                                        @elseif ($log['done'])
                                            <span class="co-log-badge co-log-done">✓ cleared</span>
                                        @else
                                            <span class="co-log-badge co-log-open">at sea</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            </div>
            <button class="co-edge-toggle" wire:click="toggle" title="Roll up the orders" aria-label="Collapse Captain's Orders">◀</button>
        </aside>
    @endif
</div>
