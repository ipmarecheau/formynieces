<div>
    @script
    <script>
        // Mirror the tester's identity into localStorage so it outlives the server session.
        Livewire.on('tester-identity-saved', (e) => {
            const d = Array.isArray(e) ? e[0] : e;
            try {
                localStorage.setItem('ut_email', d.email ?? '');
                localStorage.setItem('ut_name', d.name ?? '');
            } catch (err) {}
        });
        Livewire.on('tester-identity-cleared', () => {
            try {
                localStorage.removeItem('ut_email');
                localStorage.removeItem('ut_name');
            } catch (err) {}
        });
    </script>
    @endscript

    <div class="ut-top">
        <div class="ut-brand">Smooth<span>Seas</span> · Test Drive</div>
        @if ($testerEmail !== '')
            <div class="ut-who">{{ $testerName ?: $testerEmail }} ·
                <a wire:click="signOut"
                   x-on:click="try { localStorage.removeItem('ut_email'); localStorage.removeItem('ut_name'); } catch (e) {}">sign out</a>
            </div>
        @endif
    </div>

    @if ($testerEmail === '')
        {{-- ── Sign in with email ──────────────────────────────────── --}}
        {{-- On arrival, if this browser already knows the tester, sign them straight back in so
             their email (and the progress saved against it) is restored without retyping. --}}
        <div class="ut-card ut-narrow"
             x-data
             x-init="(() => {
                 // Only on a genuine page load — never after an in-page sign-out (which would
                 // otherwise re-run this on the Livewire morph and sign the tester straight back in).
                 if (window.__utIdentityChecked) { return; }
                 window.__utIdentityChecked = true;
                 try {
                     const email = localStorage.getItem('ut_email');
                     const name = localStorage.getItem('ut_name') || '';
                     if (email) { $wire.restoreIdentity(email, name); }
                 } catch (e) {}
             })()">
            <h1>Welcome — thanks for helping! 🧭</h1>
            <p class="lead" style="margin: 10px 0 18px;">
                We'll walk you through a few quick things to try, one at a time, with the real
                screen right here beside the steps. Just follow along and tap how it went —
                if something looks off, that's exactly what we want to know.
            </p>
            <label for="email" style="font-weight:700; display:block; margin-bottom:8px;">Your email</label>
            <input id="email" type="email" class="ut-input" placeholder="you@example.com"
                   wire:model="testerEmail" style="margin-bottom:14px;">
            @error('testerEmail') <p class="ut-err">{{ $message }}</p> @enderror

            <label for="name" style="font-weight:700; display:block; margin-bottom:8px;">Your name</label>
            <input id="name" type="text" class="ut-input" placeholder="e.g. Maria"
                   wire:model="testerName" wire:keydown.enter="signIn" style="margin-bottom:14px;">
            @error('testerName') <p class="ut-err">{{ $message }}</p> @enderror

            <p class="ut-note-hint" style="margin-bottom:14px;">
                We use your email only to save your place, so you can stop and pick up later — even on another device.
            </p>
            <button class="ut-btn" wire:click="signIn">Start testing →</button>
        </div>

    @elseif ($activeFeature === null)
        {{-- ── Feature chooser ─────────────────────────────────────── --}}
        <div class="ut-card ut-narrow">
            <h1>Pick something to test</h1>
            <p class="lead" style="margin-top:8px;">Each one takes just a few minutes. Start anywhere — your place is saved.</p>
        </div>
        <div class="ut-narrow">
            @foreach ($features as $code => $f)
                @php $count = $done[$code] ?? 0; $tot = count($f['scenarios']); @endphp
                <a class="ut-feature" href="{{ route('user-testing.feature', ['feature' => strtolower($code)]) }}" wire:navigate>
                    <div>
                        <div class="fname">{{ $f['name'] }} @isset($f['device'])<span class="ut-chip">📱 needs the app</span>@endisset</div>
                        <div class="fblurb">{{ $f['blurb'] }}</div>
                    </div>
                    <div class="ut-pill {{ $count >= $tot ? 'done' : '' }}">
                        {{ $count >= $tot ? '✓ done' : $count.' / '.$tot }}
                    </div>
                </a>
            @endforeach
        </div>

    @elseif ($current === null)
        {{-- ── Feature finished ────────────────────────────────────── --}}
        <div class="ut-card ut-narrow" style="text-align:center;">
            <div class="ut-done-emoji">🎉</div>
            <h1>All done with “{{ $activeFeature['name'] }}”!</h1>
            <p class="lead" style="margin:10px 0 18px;">
                Thank you, {{ $testerName ?: 'friend' }} — your answers are saved for the team to review.
            </p>
            <button class="ut-btn" wire:click="goTo(0)">Review your answers</button>
            <a class="ut-btn ghost" style="margin-top:10px;" href="{{ route('user-testing') }}" wire:navigate>Test something else</a>
        </div>

    @elseif ($isDevice)
        {{-- ── A device scenario: install the app, grant permissions, use it ── --}}
        <div style="margin-bottom:10px;">
            <div class="ut-count">{{ $activeFeature['name'] }} — {{ $index + 1 }} of {{ $total }}</div>
            <div class="ut-progress-track">
                <div class="ut-progress-fill" style="width: {{ $total ? ($index / $total) * 100 : 0 }}%;"></div>
            </div>
        </div>

        <div class="ut-split" x-data="{ setup: true }">
            {{-- Left: install + permissions (do once) --}}
            <div class="ut-pane">
                <div class="ut-card">
                    <div class="ut-phonebadge">📱 On your phone or tablet</div>
                    <h2 style="margin:8px 0 6px;">Install the app (once)</h2>
                    <p class="lead">{{ $activeFeature['device']['intro'] }}</p>
                    <div class="ut-qrbox">
                        <img class="ut-qr" src="{{ $qr }}" alt="QR code to install the app">
                        <a class="ut-btn ghost" href="{{ $apkUrl }}" target="_blank" rel="noopener" style="margin-top:12px;">Or tap to download the app</a>
                    </div>
                    <div style="font-weight:800; margin:16px 0 8px;">Allow these when asked</div>
                    @foreach ($activeFeature['device']['permissions'] as $perm)
                        <div class="ut-perm">
                            <div class="ut-perm-ic">{{ $perm['icon'] }}</div>
                            <div>
                                <div style="font-weight:800;">{{ $perm['title'] }}</div>
                                <div class="ut-note-hint">{{ $perm['how'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Right: the scenario to try + verdict --}}
            <div class="ut-pane">
                <div class="ut-card" wire:key="dscn-{{ $current['id'] }}">
                    <span class="ut-tag">{{ $current['id'] }}</span>
                    <h2 style="margin:10px 0 6px;">{{ $current['title'] }}</h2>
                    <p class="lead">{{ $current['goal'] }}</p>

                    <div style="font-weight:800; margin:16px 0 4px;">What to do</div>
                    <ol class="ut-steps">
                        @foreach ($current['steps'] as $step)
                            <li>{{ $step }}</li>
                        @endforeach
                    </ol>

                    <div class="ut-expect">✅ <b>It worked if:</b> {{ $current['expect'] }}</div>

                    @isset($current['note'])
                        <div class="ut-note-box">ℹ️ {{ $current['note'] }}</div>
                    @endisset

                    <div style="margin-top:16px;">
                        <label for="dnotes" style="font-weight:700; display:block; margin-bottom:6px;">Anything to add? (optional)</label>
                        <textarea id="dnotes" class="ut-input" wire:model="notes"
                                  placeholder="e.g. The permission prompt never appeared…"></textarea>
                    </div>

                    @if ($currentVerdict)
                        <p class="ut-note-hint" style="margin-top:10px;">
                            Your last answer: <b>{{ ['worked' => '✅ It worked', 'issue' => '⚠️ Something was off', 'skipped' => '⏭️ Skipped'][$currentVerdict] ?? $currentVerdict }}</b> — tap again to change it.
                        </p>
                    @endif

                    <div class="ut-verdicts">
                        <button class="ut-verdict v-good {{ $currentVerdict === 'worked' ? 'sel' : '' }}" wire:click="record('worked')">✅ It worked</button>
                        <button class="ut-verdict v-warn {{ $currentVerdict === 'issue' ? 'sel' : '' }}" wire:click="record('issue')">⚠️ Something was off</button>
                        <button class="ut-verdict v-skip {{ $currentVerdict === 'skipped' ? 'sel' : '' }}" wire:click="record('skipped')">⏭️ Skip for now</button>
                    </div>
                </div>

                <div class="ut-nav">
                    <button class="ut-navbtn" wire:click="prev" @disabled($index === 0)>← Back</button>
                    <a class="ut-navbtn ghost" href="{{ route('user-testing') }}" wire:navigate>All features</a>
                    <button class="ut-navbtn" wire:click="next">Next →</button>
                </div>
            </div>
        </div>

    @else
        {{-- ── A scenario: steps + embedded live preview ───────────── --}}
        <div class="ut-split" x-data="{ device: (localStorage.getItem('ut_device') || 'mobile') }"
             x-init="$watch('device', v => localStorage.setItem('ut_device', v))">

            {{-- Left: the instructions + verdict --}}
            <div class="ut-pane ut-pane-steps">
                <div class="ut-count">{{ $activeFeature['name'] }} — {{ $index + 1 }} of {{ $total }}</div>
                <div class="ut-progress-track">
                    <div class="ut-progress-fill" style="width: {{ $total ? ($index / $total) * 100 : 0 }}%;"></div>
                </div>

                <div class="ut-card" wire:key="scn-{{ $current['id'] }}" style="margin-top:12px;">
                    <span class="ut-tag">{{ $current['id'] }}</span>
                    <h2 style="margin:10px 0 6px;">{{ $current['title'] }}</h2>
                    <p class="lead">{{ $current['goal'] }}</p>

                    <div style="font-weight:800; margin:16px 0 4px;">What to do</div>
                    <ol class="ut-steps">
                        @foreach ($current['steps'] as $step)
                            <li>{{ $step }}</li>
                        @endforeach
                    </ol>

                    <div class="ut-expect">✅ <b>It worked if:</b> {{ $current['expect'] }}</div>

                    <div style="margin-top:16px;">
                        <label for="notes" style="font-weight:700; display:block; margin-bottom:6px;">Anything to add? (optional)</label>
                        <textarea id="notes" class="ut-input" wire:model="notes"
                                  placeholder="e.g. The button was hard to find, or it felt slow…"></textarea>
                    </div>

                    @if ($currentVerdict)
                        <p class="ut-note-hint" style="margin-top:10px;">
                            Your last answer: <b>{{ ['worked' => '✅ It worked', 'issue' => '⚠️ Something was off', 'skipped' => '⏭️ Skipped'][$currentVerdict] ?? $currentVerdict }}</b> — tap again to change it.
                        </p>
                    @endif

                    <div class="ut-verdicts">
                        <button class="ut-verdict v-good {{ $currentVerdict === 'worked' ? 'sel' : '' }}" wire:click="record('worked')">✅ It worked</button>
                        <button class="ut-verdict v-warn {{ $currentVerdict === 'issue' ? 'sel' : '' }}" wire:click="record('issue')">⚠️ Something was off</button>
                        <button class="ut-verdict v-skip {{ $currentVerdict === 'skipped' ? 'sel' : '' }}" wire:click="record('skipped')">⏭️ Skip for now</button>
                    </div>
                </div>

                <div class="ut-nav">
                    <button class="ut-navbtn" wire:click="prev" @disabled($index === 0)>← Back</button>
                    <a class="ut-navbtn ghost" href="{{ route('user-testing') }}" wire:navigate>All features</a>
                    <button class="ut-navbtn" wire:click="next">Next →</button>
                </div>
            </div>

            {{-- Right: the real screen, embedded, at a chosen viewport --}}
            <div class="ut-pane ut-pane-preview">
                <div class="ut-devbar">
                    <span class="ut-devlabel">Viewing as:</span>
                    <button class="ut-dev" :class="{ 'on': device==='mobile' }" @click="device='mobile'">📱 Mobile</button>
                    <button class="ut-dev" :class="{ 'on': device==='tablet' }" @click="device='tablet'">📲 Tablet</button>
                    <button class="ut-dev" :class="{ 'on': device==='web' }" @click="device='web'">💻 Web</button>
                    <a class="ut-dev ghost" href="{{ route('user-testing.enter', $current['id']) }}" target="_blank" rel="noopener" title="Open in a real new tab">↗</a>
                </div>
                <div class="ut-stage">
                    <div class="ut-device" :class="'is-'+device"
                         :style="device==='mobile' ? 'width:390px' : (device==='tablet' ? 'width:834px' : 'width:100%')">
                        <iframe class="ut-frame" src="{{ route('user-testing.enter', $current['id']) }}"
                                wire:key="frame-{{ $current['id'] }}"
                                {{-- Scenarios like HC-03 record a voice note; getUserMedia is blocked
                                     inside an iframe unless the embed explicitly grants it. --}}
                                allow="microphone; camera; clipboard-write"
                                title="Live preview of the screen to test"></iframe>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
