{{-- AG-06: shown instead of a lesson/tutorial once the 2-hour daily guided pool is spent.
     Kind, framed as a rest — and practice stays open, so she can always keep progressing. --}}
<style>
    .gl-wrap { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px 20px 48px; text-align: center; }
    .gl-card { background: #0c2440; border: 1.5px solid rgba(34,211,238,0.35); border-radius: 24px; padding: 40px 30px; width: 100%; max-width: 460px; }
    .gl-avatar { width: 110px; height: 110px; object-fit: contain; margin: 0 auto 12px; display: block; filter: drop-shadow(0 8px 18px rgba(0,0,0,0.4)); }
    .gl-head { font-family: 'Fredoka One', cursive; font-size: 24px; color: #67e8f9; margin: 0 0 14px; }
    .gl-lead { font-size: 17px; line-height: 1.6; color: rgba(243,232,255,0.92); margin-bottom: 14px; }
    .gl-open { font-size: 16px; color: #86efac; font-weight: 700; margin-bottom: 26px; }
    .gl-cta { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    .gl-btn { display: inline-block; background: linear-gradient(135deg, #0e7490, #f6b71e); border: none; border-radius: 999px; padding: 14px 30px; color: #fff; font-family: 'Fredoka One', cursive; font-size: 16px; text-decoration: none; }
    .gl-btn.secondary { background: rgba(255,255,255,0.08); border: 2px solid rgba(34,211,238,0.4); color: #e6f2fb; }
</style>
<div class="gl-wrap">
    <div class="gl-card">
        <img class="gl-avatar" src="{{ asset('images/voyage/companion/smooth-chart.webp') }}" alt="Smooth the turtle">
        <p class="gl-head">Great learning today! 🌟</p>
        <p class="gl-lead">That is your guided lessons and tutorials done for today — nice work. Let it sink in and come back tomorrow for more.</p>
        <p class="gl-open">Practice is always open — keep your streak going!</p>
        <div class="gl-cta">
            <a href="{{ route('practice.walk', $moduleId) }}" class="gl-btn">Keep practising →</a>
            <a href="{{ route('student.voyage') }}" class="gl-btn secondary">Back to my voyage</a>
        </div>
    </div>
</div>
