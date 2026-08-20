<div>
    @if ($open)
        <style>
            .it-hole { position: fixed; z-index: 2000; border-radius: 12px; pointer-events: none; box-shadow: 0 0 0 9999px rgba(6,20,34,0.55); outline: 3px solid #f6b71e; outline-offset: 3px; transition: all .25s ease; }
            .it-card { position: fixed; left: 50%; transform: translateX(-50%); bottom: 16px; z-index: 2002; width: min(92vw, 360px); background: linear-gradient(160deg, #0e2438, #14324a); border: 1.5px solid rgba(246,183,30,0.55); border-radius: 18px; box-shadow: 0 14px 40px rgba(0,0,0,0.5); padding: 14px 16px 13px; color: #e6f2fb; }
            .it-head { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; }
            .it-avatar { width: 46px; height: 46px; object-fit: contain; flex: none; }
            .it-title { font-size: 1.08rem; font-weight: 900; }
            .it-line { font-size: 0.9rem; font-weight: 600; line-height: 1.42; color: #cfe8fb; margin: 2px 0 10px; }
            .it-handoff { text-align: center; font-size: 0.98rem; font-weight: 900; color: #fde68a; margin: 2px 0; }
            .it-skip { display: block; margin: 9px auto 0; background: none; border: none; color: #8fb6d6; font-size: 0.78rem; font-weight: 700; cursor: pointer; text-decoration: underline; }
        </style>

        {{-- Visibility is server-driven (@if) so this works on layouts that load a
             second Alpine; plain Alpine below only spotlights the first stop. --}}
        <div x-data="{
                holeStyle: '', hasHole: false,
                place() {
                    const el = document.querySelector('.vy-stop:not(.is-locked)');
                    if (!el) { this.hasHole = false; return; }
                    el.scrollIntoView({ behavior: 'auto', block: 'center' });
                    this.$nextTick(() => { const r = el.getBoundingClientRect(); const p = 6;
                        this.holeStyle = `top:${r.top-p}px;left:${r.left-p}px;width:${r.width+p*2}px;height:${r.height+p*2}px;`;
                        this.hasHole = true; });
                },
             }"
             x-init="$nextTick(() => place())">
            <div class="it-hole" x-show="hasHole" :style="holeStyle"></div>
            <div class="it-card">
                <div class="it-head">
                    <img class="it-avatar" src="{{ $this->avatarUrl() }}" alt="Smooth the turtle">
                    <div class="it-title">This island’s stops</div>
                </div>
                <p class="it-line">Each stop is a level to conquer — follow the trail in order. Let’s open the first one!</p>
                <p class="it-handoff">👆 Tap the glowing stop to open its lesson</p>
                <button type="button" class="it-skip" wire:click="skip">Skip the tour</button>
            </div>
        </div>
    @endif
</div>
