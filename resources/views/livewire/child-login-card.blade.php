<div>
    <style>
        .clc{border:1px solid var(--line);border-radius:16px;background:var(--paper-2);padding:16px 18px;box-shadow:var(--shadow-sm);}
        .clc-t{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:800;letter-spacing:.09em;text-transform:uppercase;color:var(--teal);margin:0 0 12px;}
        .clc-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:9px 0;}
        .clc-row + .clc-row{border-top:1px solid var(--line);}
        .clc-k{font-size:11px;color:var(--ink-faint);font-weight:800;margin:0;}
        .clc-v{font-size:14.5px;font-weight:800;color:var(--ink);margin:3px 0 0;font-variant-numeric:tabular-nums;letter-spacing:.02em;word-break:break-all;}
        .clc-obf{letter-spacing:2px;color:var(--ink-faint);}
        .clc-btn{font-size:12px;font-weight:800;border:0;border-radius:9px;padding:8px 12px;cursor:pointer;white-space:nowrap;}
        .clc-reveal{color:var(--teal-deep);background:var(--teal-tint);}
        .clc-copy{color:var(--ink-faint);background:none;}
        .clc-hint{font-size:11.5px;color:var(--ink-soft);font-weight:700;background:var(--teal-tint);border-radius:10px;padding:9px 11px;margin:12px 0 0;}
        .clc-foot{display:flex;flex-direction:column;gap:8px;margin-top:11px;}
        .clc-foot a,.clc-foot button{display:block;width:100%;text-align:center;text-decoration:none;font-size:13.5px;font-weight:800;border-radius:11px;padding:12px 10px;border:1px solid var(--line);color:var(--teal-deep);background:var(--paper-2);cursor:pointer;}
        .clc-foot a.clc-solid{background:var(--teal);color:#fff;border-color:var(--teal);}
        .clc-od{margin-top:11px;border:1px solid var(--line);border-radius:12px;background:var(--teal-tint);padding:12px 13px;}
        .clc-od p{margin:0;}
        .clc-od-t{font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--teal-deep);margin:0 0 7px;}
        .clc-od-url{font-size:15px;font-weight:800;color:var(--ink);font-variant-numeric:tabular-nums;word-break:break-all;background:var(--paper-2);border:1px solid var(--line);border-radius:9px;padding:8px 10px;display:flex;align-items:center;justify-content:space-between;gap:10px;}
        .clc-od ol{margin:9px 0 0;padding-left:18px;font-size:12.5px;color:var(--ink-soft);font-weight:700;line-height:1.6;}
    </style>

    <section class="clc" aria-label="Your child's login">
        <p class="clc-t">🔑 {{ $child->name }}'s login</p>

        <div class="clc-row">
            <div>
                <p class="clc-k">Login ID</p>
                <p class="clc-v" id="clc-id-{{ $child->id }}">{{ $child->email }}</p>
            </div>
            <button type="button" class="clc-btn clc-copy" onclick="clcCopy(this,'{{ $child->email }}')">Copy</button>
        </div>

        <div class="clc-row">
            <div>
                <p class="clc-k">Password</p>
                <p class="clc-v">
                    @if ($password !== null)
                        <span id="clc-pw-{{ $child->id }}">{{ $password }}</span>
                    @else
                        <span class="clc-obf">••••••••••</span>
                    @endif
                </p>
            </div>
            @if ($password !== null)
                <button type="button" class="clc-btn clc-copy" onclick="clcCopy(this,@js($password))">Copy</button>
                <button type="button" class="clc-btn clc-reveal" wire:click="toggleReveal">Hide</button>
            @else
                <button type="button" class="clc-btn clc-reveal" wire:click="toggleReveal">👁 Reveal</button>
            @endif
        </div>

        <p class="clc-hint">🧭 {{ $child->name }} signs in with this — not you. On their own device, go to <strong>smoothseas.org/go</strong>. Sharing this device? Hand it over below.</p>

        <div class="clc-foot">
            <a href="{{ route('student.handoff', $child) }}" class="clc-solid">📱 Log in {{ $child->name }} on this device</a>
            <button type="button" wire:click="toggleOtherDevice">🖥 Log in {{ $child->name }} on another device</button>
        </div>
        <p style="text-align:center;margin:9px 0 0;"><a href="{{ route('guardian.children') }}" style="font-size:11.5px;font-weight:800;color:var(--ink-faint);text-decoration:none;">Reset login</a></p>

        @if ($showOtherDevice)
            <div class="clc-od">
                <p class="clc-od-t">On {{ $child->name }}'s own device</p>
                <div class="clc-od-url">
                    <span>{{ route('student.login') }}</span>
                    <button type="button" class="clc-btn clc-copy" onclick="clcCopy(this,'{{ route('student.login') }}')">Copy</button>
                </div>
                <ol>
                    <li>Open that link on {{ $child->name }}'s device.</li>
                    <li>Enter the Login ID and Password above.</li>
                    <li>Tap <strong>Set sail →</strong>.</li>
                </ol>
            </div>
        @endif
    </section>

    <script>
        function clcCopy(btn, txt){
            try { navigator.clipboard.writeText(txt); } catch (e) {}
            const o = btn.textContent; btn.textContent = 'Copied ✓';
            setTimeout(() => { btn.textContent = o; }, 1200);
        }
    </script>
</div>
