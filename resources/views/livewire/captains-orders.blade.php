<div class="co-root" wire:key="captains-orders">
    {{-- Rustic voyage sidebar. Emoji stand in for art Isaac will supply:
         🐢 Smooth · 📜 scroll · ⚓/🌬️/🛟/🏝️ reward tokens. Swap for real assets later. --}}
    <style>
        .co-root { font-family: 'Nunito', sans-serif; }

        /* Collapsed rail — a rolled scroll pinned to the mast */
        .co-rail {
            position: fixed; left: 0; top: 96px; z-index: 40;
            display: flex; flex-direction: column; align-items: center; gap: 6px;
            padding: 14px 8px; cursor: pointer;
            background: linear-gradient(180deg, #6b4a2b, #4a3119);
            border: 2px solid #3a2712; border-left: none;
            border-radius: 0 12px 12px 0;
            box-shadow: 3px 4px 10px rgba(0,0,0,0.4);
            color: #f0e0bd;
        }
        .co-rail-ico { font-size: 1.5rem; }
        .co-rail-label {
            font-family: 'Fredoka One', cursive; font-size: 0.7rem;
            writing-mode: vertical-rl; letter-spacing: 1px;
        }

        /* Expanded parchment panel in a wood frame */
        .co-panel {
            position: fixed; left: 0; top: 88px; z-index: 40;
            width: min(310px, 84vw); max-height: calc(100vh - 108px);
            overflow-y: auto;
        }
        .co-frame {
            background:
                repeating-linear-gradient(90deg, transparent 0 6px, rgba(90,61,33,0.06) 6px 7px),
                linear-gradient(160deg, #f4e8c8 0%, #ecdcb0 55%, #e3cf9c 100%);
            border: 6px solid #5a3d21;
            border-radius: 4px 14px 14px 4px;
            box-shadow: 4px 6px 18px rgba(0,0,0,0.45), inset 0 0 34px rgba(120,84,40,0.28);
            color: #3d2b16;
            padding: 0 0 14px;
        }
        /* rope trim */
        .co-frame::before {
            content: ""; display: block; height: 5px;
            background: repeating-linear-gradient(45deg, #b98a4b 0 6px, #8a6531 6px 12px);
        }

        .co-head {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 12px 8px;
        }
        .co-crest {
            width: 40px; height: 40px; flex: none;
            display: grid; place-items: center; font-size: 1.4rem;
            background: radial-gradient(circle at 35% 30%, #fbe9c0, #c9791f);
            border: 2px solid #7a4a1a; border-radius: 50%;
            box-shadow: inset 0 -2px 6px rgba(0,0,0,0.25);
        }
        .co-title-main { font-family: 'Fredoka One', cursive; font-size: 1.15rem; color: #4a3119; line-height: 1; }
        .co-title-sub { font-size: 0.72rem; font-weight: 800; color: #8a6531; text-transform: uppercase; letter-spacing: 1px; }
        .co-collapse {
            margin-left: auto; border: none; cursor: pointer;
            width: 26px; height: 26px; border-radius: 50%;
            background: #9e3b23; color: #f4e8c8; font-weight: 900; font-size: 0.8rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .co-tabs { display: flex; gap: 4px; padding: 4px 12px 10px; }
        .co-tab {
            flex: 1; cursor: pointer; padding: 6px 8px;
            font-family: 'Fredoka One', cursive; font-size: 0.82rem;
            color: #7a5a2e; background: rgba(90,61,33,0.08);
            border: 1.5px solid #b98a4b; border-radius: 8px;
        }
        .co-tab.is-on { color: #f4e8c8; background: linear-gradient(135deg, #6b4a2b, #8a6531); }

        .co-body { padding: 0 14px; }
        .co-lead { font-size: 0.86rem; font-weight: 700; margin: 2px 0 10px; }

        /* Checklist */
        .co-checklist { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 7px; }
        .co-duty {
            display: flex; align-items: center; gap: 9px;
            padding: 8px 10px; border-radius: 9px;
            background: rgba(255,255,255,0.4);
            border: 1.5px dashed #b98a4b;
            font-size: 0.86rem; font-weight: 700;
        }
        .co-duty.is-done { background: rgba(52,125,91,0.16); border-style: solid; border-color: #2f7d5b; color: #245c43; }
        .co-check {
            width: 20px; height: 20px; flex: none; border-radius: 50%;
            display: grid; place-items: center; font-size: 0.8rem; font-weight: 900;
            border: 2px solid #b98a4b; color: #2f7d5b;
        }
        .co-duty.is-done .co-check { background: #2f7d5b; color: #fff; border-color: #2f7d5b; }
        .co-duty-label { flex: 1; }
        .co-duty-note { font-size: 0.68rem; font-weight: 800; color: #8a6531; text-transform: uppercase; }
        .co-duty-do {
            cursor: pointer; border: none; border-radius: 6px; padding: 3px 8px;
            font-size: 0.68rem; font-weight: 800; color: #f4e8c8; background: #6b4a2b;
        }
        .co-soon { font-size: 0.6rem; font-weight: 800; color: #9e3b23; text-transform: uppercase; }

        .co-gate {
            margin: 12px 0 0; padding: 9px 10px; font-size: 0.8rem; font-weight: 700;
            background: rgba(201,121,31,0.16); border-left: 4px solid #c9791f; border-radius: 6px; color: #7a4a1a;
        }
        .co-alldone { margin: 12px 0 0; font-family: 'Fredoka One', cursive; color: #2f7d5b; text-align: center; }
        .co-review {
            margin: 12px 0 0; width: 100%; cursor: pointer;
            background: none; border: 1.5px solid #b98a4b; border-radius: 8px;
            padding: 7px; font-weight: 800; color: #7a5a2e;
        }

        /* Rest day */
        .co-rest { text-align: center; padding: 18px 6px; }
        .co-rest-ico { font-size: 2.2rem; }
        .co-rest-txt { font-size: 0.9rem; font-weight: 700; margin: 8px 0 0; }

        /* Ship's Log */
        .co-streak-hero { text-align: center; padding: 8px 0 12px; }
        .co-streak-num { font-family: 'Fredoka One', cursive; font-size: 2.6rem; color: #c9791f; line-height: 1; }
        .co-streak-cap { font-size: 0.78rem; font-weight: 800; color: #7a5a2e; text-transform: uppercase; letter-spacing: 1px; }
        .co-substreaks { list-style: none; margin: 0 0 14px; padding: 0; display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
        .co-substreaks li {
            display: flex; justify-content: space-between; align-items: center;
            padding: 6px 9px; border-radius: 7px; background: rgba(255,255,255,0.4);
            border: 1px solid #d9c396; font-size: 0.8rem; font-weight: 700;
        }
        .co-sub-num { font-family: 'Fredoka One', cursive; color: #8a6531; }

        .co-locker-title { font-family: 'Fredoka One', cursive; font-size: 0.95rem; color: #4a3119; margin: 4px 0 8px; }
        .co-reward {
            display: flex; align-items: center; gap: 9px; margin-bottom: 7px;
            padding: 8px 10px; border-radius: 9px;
            background: rgba(255,255,255,0.45); border: 1.5px solid #b98a4b;
        }
        .co-reward-ico { font-size: 1.35rem; flex: none; }
        .co-reward-name { font-size: 0.82rem; font-weight: 800; color: #4a3119; }
        .co-reward-count { color: #9e3b23; }
        .co-reward-blurb { font-size: 0.7rem; color: #7a5a2e; }
        .co-reward-body { flex: 1; }
        .co-reward-use {
            cursor: pointer; border: none; border-radius: 6px; padding: 4px 10px;
            font-size: 0.72rem; font-weight: 800; color: #f4e8c8;
            background: linear-gradient(135deg, #6b4a2b, #8a6531);
        }

        @media (max-width: 640px) {
            .co-panel { top: auto; bottom: 0; left: 0; width: 100vw; max-height: 52vh; border-radius: 0; }
            .co-frame { border-radius: 0; border-left: none; border-right: none; }
            .co-rail { top: auto; bottom: 12px; }
        }
    </style>

    @if ($collapsed)
        <button class="co-rail" wire:click="toggle" title="Open Captain's Orders">
            <span class="co-rail-ico">📜</span>
            <span class="co-rail-label">Orders</span>
        </button>
    @else
        <aside class="co-panel">
            <div class="co-frame">
                <header class="co-head">
                    <div class="co-crest">🐢</div>
                    <div>
                        <div class="co-title-main">Captain's Orders</div>
                        <div class="co-title-sub">{{ $isEvening ? 'Evening watch' : 'Morning muster' }}</div>
                    </div>
                    <button class="co-collapse" wire:click="toggle" title="Roll up the orders">✕</button>
                </header>

                <nav class="co-tabs">
                    <button class="co-tab {{ $tab === 'brief' ? 'is-on' : '' }}" wire:click="showTab('brief')">Brief</button>
                    <button class="co-tab {{ $tab === 'log' ? 'is-on' : '' }}" wire:click="showTab('log')">Ship's Log</button>
                </nav>

                @if ($tab === 'brief')
                    <div class="co-body">
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
                                            @if ($duty['key'] === 'map')
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
                                <button class="co-review" wire:click="showTab('log')">Look back on today ›</button>
                            @endif
                        @endif
                    </div>
                @else
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

                        <div class="co-locker">
                            <h4 class="co-locker-title">🧰 Captain's Locker</h4>
                            @foreach ($rewards as $r)
                                <div class="co-reward">
                                    <span class="co-reward-ico">{{ ['shore_leave' => '🏝️', 'anchor' => '⚓', 'tailwind' => '🌬️', 'lifebuoy' => '🛟'][$r['type']] }}</span>
                                    <div class="co-reward-body">
                                        <div class="co-reward-name">{{ $r['label'] }} <span class="co-reward-count">×{{ $r['held'] }}</span></div>
                                        <div class="co-reward-blurb">{{ $r['blurb'] }}</div>
                                    </div>
                                    @if ($r['held'] > 0)
                                        <button class="co-reward-use" wire:click="useReward('{{ $r['type'] }}')">Use</button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </aside>
    @endif
</div>
