{{--
    Voyage flash toast — shows a kind message when the student is sailed back to the map by
    a gate (e.g. the writing gate, or a locked stage), so a redirect is never a SILENT bounce.
    Reads the student flash keys and renders the first one present. Include right after <body>
    on the standalone voyage pages (overworld + island).
--}}
@php
    $voyageFlash = session('writingGate') ?? session('lockMessage') ?? session('voyageNote');
@endphp
@if ($voyageFlash)
    <div class="vy-flash" id="vy-flash" role="status">
        <span class="vy-flash-text">{{ $voyageFlash }}</span>
        <button type="button" class="vy-flash-x" aria-label="Dismiss" onclick="this.closest('.vy-flash').remove()">×</button>
    </div>
    <style>
        .vy-flash {
            position: fixed; top: 14px; left: 50%; transform: translateX(-50%);
            z-index: 1000; max-width: min(92vw, 460px);
            display: flex; align-items: center; gap: 12px;
            padding: 13px 16px;
            background: linear-gradient(160deg, #fbe3a8, #f6c667);
            color: #5a3d0c; font-family: 'Nunito', system-ui, sans-serif; font-weight: 800;
            font-size: 14.5px; line-height: 1.35;
            border: 1.5px solid #e2a93b; border-radius: 14px;
            box-shadow: 0 16px 40px -14px rgba(0,0,0,.55);
            animation: vyFlashIn .35s ease;
        }
        .vy-flash-text { flex: 1; }
        .vy-flash-x {
            flex: none; border: 0; background: rgba(0,0,0,.08); color: #5a3d0c;
            width: 26px; height: 26px; border-radius: 50%; font-size: 18px; font-weight: 900;
            cursor: pointer; line-height: 1;
        }
        .vy-flash-x:hover { background: rgba(0,0,0,.16); }
        @keyframes vyFlashIn { from { opacity: 0; transform: translateX(-50%) translateY(-10px); } }
    </style>
    <script>
        // Auto-dismiss after a few seconds (the student can also tap ×).
        setTimeout(function () { var el = document.getElementById('vy-flash'); if (el) el.remove(); }, 6500);
    </script>
@endif
