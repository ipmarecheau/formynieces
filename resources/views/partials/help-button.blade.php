{{-- In-app help button — appears on every authed/public screen. A user types a message
     or records a voice note the instant they hit friction; it posts to /help/ticket and
     lands in the admin Support inbox. Self-contained styles + CSRF; safe on any layout. --}}
<style>
    .ssh-fab {
        position: fixed; left: 18px; bottom: calc(76px + env(safe-area-inset-bottom)); z-index: 2147483000;
        display: inline-flex; align-items: center; gap: 8px;
        padding: 11px 16px; border-radius: 999px; border: none; cursor: pointer;
        background: #0d7d8c; color: #fff; font-family: 'Nunito', system-ui, sans-serif;
        font-size: 14px; font-weight: 800; box-shadow: 0 8px 22px rgba(0,0,0,.28);
        transition: transform .15s, background .15s;
    }
    .ssh-fab:hover { transform: translateY(-1px); background: #0a5c68; }
    .ssh-fab svg { width: 18px; height: 18px; }
    .ssh-panel {
        position: fixed; left: 18px; bottom: calc(132px + env(safe-area-inset-bottom)); z-index: 2147483000;
        width: 320px; max-width: calc(100vw - 36px);
        background: #ffffff; color: #12222e; border: 1px solid #e7ddcd; border-radius: 18px;
        box-shadow: 0 24px 60px rgba(0,0,0,.35); overflow: hidden;
        opacity: 0; transform: translateY(10px) scale(.98); pointer-events: none;
        transition: opacity .2s ease, transform .2s ease;
        font-family: 'Nunito', system-ui, sans-serif;
    }
    .ssh-panel.open { opacity: 1; transform: none; pointer-events: auto; }
    .ssh-head { display: flex; align-items: center; gap: 8px; padding: 14px 16px 10px; }
    .ssh-head .t { font-family: 'Fredoka', 'Nunito', sans-serif; font-weight: 700; font-size: 16px; color: #12222e; }
    .ssh-head .x { margin-left: auto; background: none; border: none; color: #6b8199; font-size: 18px; cursor: pointer; line-height: 1; padding: 4px; }
    .ssh-body { padding: 0 16px 16px; }
    .ssh-kinds { display: flex; gap: 6px; margin-bottom: 10px; }
    .ssh-kind { flex: 1; padding: 7px 4px; border-radius: 999px; border: 1.5px solid #e7ddcd; background: #fff;
        color: #40566a; font-size: 12.5px; font-weight: 800; cursor: pointer; }
    .ssh-kind.on { background: #0d7d8c; border-color: #0d7d8c; color: #fff; }
    .ssh-ta {
        width: 100%; min-height: 84px; resize: vertical; padding: 10px 12px;
        border: 1.5px solid #e7ddcd; border-radius: 12px; font-family: inherit; font-size: 14px;
        color: #12222e; outline: none;
    }
    .ssh-ta:focus { border-color: #0d7d8c; }
    .ssh-rec-row { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
    .ssh-rec {
        display: inline-flex; align-items: center; gap: 7px; padding: 9px 13px; border-radius: 999px;
        border: 1.5px solid #e7ddcd; background: #fff; color: #40566a; font-size: 13px; font-weight: 800; cursor: pointer;
    }
    .ssh-rec.recording { border-color: #d14343; color: #d14343; }
    .ssh-rec .dot { width: 9px; height: 9px; border-radius: 50%; background: currentColor; }
    .ssh-rec.recording .dot { animation: sshPulse 1s infinite; }
    @keyframes sshPulse { 0%,100% { opacity: 1; } 50% { opacity: .25; } }
    .ssh-audio-note { font-size: 12px; color: #0a5c68; font-weight: 700; }
    .ssh-audio-note button { background: none; border: none; color: #d14343; cursor: pointer; font-weight: 800; }
    .ssh-send {
        width: 100%; margin-top: 12px; padding: 12px; border: none; border-radius: 12px; cursor: pointer;
        background: #f2a900; color: #3a2900; font-family: 'Fredoka','Nunito',sans-serif; font-weight: 700; font-size: 15px;
    }
    .ssh-send:disabled { opacity: .5; cursor: default; }
    .ssh-hint { font-size: 11.5px; color: #6b8199; margin-top: 8px; line-height: 1.4; }
    .ssh-ok { padding: 18px 16px 20px; text-align: center; }
    .ssh-ok .big { font-size: 30px; }
    .ssh-ok .t { font-family: 'Fredoka','Nunito',sans-serif; font-weight: 700; font-size: 16px; margin: 6px 0 4px; }
    .ssh-ok .s { font-size: 13px; color: #40566a; }
    @media (prefers-reduced-motion: reduce) { .ssh-panel, .ssh-fab { transition: none; } .ssh-rec.recording .dot { animation: none; } }
</style>

<button type="button" class="ssh-fab" id="sshFab" aria-label="Get help">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M9.09 9a3 3 0 1 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>
    </svg>
    Help
</button>

<div class="ssh-panel" id="sshPanel" role="dialog" aria-label="Get help">
    <div class="ssh-head">
        <span class="t">Need a hand?</span>
        <button type="button" class="x" id="sshClose" aria-label="Close">✕</button>
    </div>
    <div class="ssh-body" id="sshBody">
        <div class="ssh-kinds" role="tablist">
            <button type="button" class="ssh-kind on" data-kind="issue">Something's wrong</button>
            <button type="button" class="ssh-kind" data-kind="question">Question</button>
            <button type="button" class="ssh-kind" data-kind="idea">Idea</button>
        </div>
        <textarea class="ssh-ta" id="sshMsg" placeholder="Tell us what's happening — what were you trying to do?"></textarea>
        <div class="ssh-rec-row">
            <button type="button" class="ssh-rec" id="sshRec"><span class="dot"></span><span id="sshRecLabel">Record a voice note</span></button>
            <span class="ssh-audio-note" id="sshAudioNote" hidden>🎙️ voice note ready <button type="button" id="sshAudioClear">remove</button></span>
        </div>
        <button type="button" class="ssh-send" id="sshSend">Send</button>
        <p class="ssh-hint">Goes straight to the team. We read these fast and fix issues early.</p>
    </div>
</div>

<script>
(function () {
    var fab = document.getElementById('sshFab'),
        panel = document.getElementById('sshPanel'),
        closeBtn = document.getElementById('sshClose'),
        body = document.getElementById('sshBody'),
        msg = document.getElementById('sshMsg'),
        send = document.getElementById('sshSend'),
        rec = document.getElementById('sshRec'),
        recLabel = document.getElementById('sshRecLabel'),
        audioNote = document.getElementById('sshAudioNote'),
        audioClear = document.getElementById('sshAudioClear');

    var kind = 'issue', audioBlob = null, mediaRecorder = null, chunks = [];
    var CSRF = '{{ csrf_token() }}', ENDPOINT = '{{ route('support.ticket') }}';

    function toggle(open) {
        panel.classList.toggle('open', open);
        if (open) { msg.focus(); }
    }
    fab.addEventListener('click', function () { toggle(!panel.classList.contains('open')); });
    closeBtn.addEventListener('click', function () { toggle(false); });

    document.querySelectorAll('.ssh-kind').forEach(function (b) {
        b.addEventListener('click', function () {
            document.querySelectorAll('.ssh-kind').forEach(function (x) { x.classList.remove('on'); });
            b.classList.add('on');
            kind = b.getAttribute('data-kind');
        });
    });

    function clearAudio() {
        audioBlob = null; audioNote.hidden = true;
        recLabel.textContent = 'Record a voice note';
    }
    audioClear.addEventListener('click', clearAudio);

    rec.addEventListener('click', function () {
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            mediaRecorder.stop();
            return;
        }
        if (!navigator.mediaDevices || !window.MediaRecorder) {
            recLabel.textContent = 'Voice not supported — please type';
            return;
        }
        navigator.mediaDevices.getUserMedia({ audio: true }).then(function (stream) {
            chunks = [];
            mediaRecorder = new MediaRecorder(stream);
            mediaRecorder.ondataavailable = function (e) { if (e.data.size) { chunks.push(e.data); } };
            mediaRecorder.onstop = function () {
                audioBlob = new Blob(chunks, { type: mediaRecorder.mimeType || 'audio/webm' });
                stream.getTracks().forEach(function (t) { t.stop(); });
                rec.classList.remove('recording');
                recLabel.textContent = 'Re-record';
                audioNote.hidden = false;
            };
            mediaRecorder.start();
            rec.classList.add('recording');
            recLabel.textContent = 'Stop recording';
        }).catch(function () {
            recLabel.textContent = 'Mic blocked — please type';
        });
    });

    send.addEventListener('click', function () {
        var text = msg.value.trim();
        if (!text && !audioBlob) { msg.focus(); return; }
        send.disabled = true; send.textContent = 'Sending…';

        var fd = new FormData();
        fd.append('kind', kind);
        if (text) { fd.append('message', text); }
        fd.append('url', location.href);
        fd.append('context[viewport]', window.innerWidth + 'x' + window.innerHeight);
        fd.append('context[path]', location.pathname);
        if (audioBlob) {
            var ext = (audioBlob.type.indexOf('ogg') > -1) ? 'ogg' : (audioBlob.type.indexOf('mp4') > -1 ? 'mp4' : 'webm');
            fd.append('audio', audioBlob, 'voice-note.' + ext);
        }

        fetch(ENDPOINT, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: fd
        }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
          .then(function (res) {
            if (!res.ok) { throw new Error(res.d && res.d.error || 'failed'); }
            body.innerHTML = '<div class="ssh-ok"><div class="big">🙌</div><div class="t">Got it — thank you!</div>' +
                '<div class="s">We\'ve logged it and will look into it quickly.</div></div>';
            setTimeout(function () { toggle(false); }, 2200);
        }).catch(function (e) {
            send.disabled = false; send.textContent = 'Send';
            alert(e.message || 'Could not send — please try again.');
        });
    });
})();
</script>
