// Builds the reel's audio track and muxes it onto the sped video.
//   1. Piper TTS  -> one narration WAV per beat  (works with no ffmpeg)
//   2. ffmpeg     -> soft music bed, delay each line to its beat time,
//                    mix, and mux into child-reel.webm (+ .mp4 for Safari/iOS)
//
// Narration is timed from out/beats.json (raw ms) scaled by SPEED to match the
// sped video. Re-run after any re-record.  Usage: node scripts/reels/build-audio.mjs

import { execFileSync } from 'node:child_process';
import { readFileSync, mkdirSync, existsSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const dir = dirname(fileURLToPath(import.meta.url));
const OUT = join(dir, 'out');
const AUDIO = join(OUT, 'audio');
mkdirSync(AUDIO, { recursive: true });

const SPEED = 0.52; // MUST match the -itsscale used to encode the video-only base
const VIDEO = join(OUT, 'child-reel-base.webm'); // video-only source (build artifact)
const VOICE = process.env.PIPER_VOICE ??
  join(process.env.SCRATCH ?? '/tmp/claude-0/-root-dev-formynieces/94033ef5-0d34-4bf7-800c-4a9632257575/scratchpad',
    'piper-voices/en_US-amy-medium.onnx');

const NARRATION = {
  1: 'Sign in, and pick up right where you left off!',
  2: 'Every day starts warm — a quick read, and today’s new words!',
  3: 'Then a daily writing stop, with kind, specific feedback!',
  4: 'Your whole curriculum is a map — and every skill is an island to conquer!',
  5: 'Earn perks as you sail!',
  6: 'Life happens — take a day off, or rescue a streak!',
  7: 'Master any island one of three ways!',
  8: 'Ace the quick check — six right, and it’s mastered on the spot!',
  9: 'Or learn it, hands on, one idea at a time!',
  10: 'Or let Smooth re-teach it — then prove you’ve got it!',
  0: 'SmoothSeas. They always know where they’re going!',
};

// --- beat times (first occurrence per step), scaled to the sped timeline -----
const beats = JSON.parse(readFileSync(join(OUT, 'beats.json'), 'utf8'));
const seen = new Set();
const timeline = [];
for (const b of beats) {
  if (seen.has(b.step)) { continue; }
  seen.add(b.step);
  timeline.push({ step: b.step, at: Math.round(b.ms * SPEED) }); // ms in sped video
}

// --- 1. synth narration -------------------------------------------------------
function hasFfmpeg() {
  try { execFileSync('ffmpeg', ['-version'], { stdio: 'ignore' }); return true; }
  catch { return false; }
}
if (!existsSync(VOICE)) {
  console.error('Piper voice model not found at', VOICE);
  process.exit(1);
}
for (const { step } of timeline) {
  const wav = join(AUDIO, `beat-${step}.wav`);
  execFileSync('python3', ['-m', 'piper', '--model', VOICE, '--output_file', wav,
    '--length-scale', '0.92',      // a touch faster — more upbeat
    '--noise-w-scale', '0.9',      // more expressive phrasing
    '--sentence-silence', '0.0',   // trim trailing silence so clips fit their slots
  ], { input: NARRATION[step], stdio: ['pipe', 'ignore', 'inherit'] });
}
console.log(`Synthesized ${timeline.length} narration clips into`, AUDIO);

// --- 2. mux (needs system ffmpeg) --------------------------------------------
if (!hasFfmpeg()) {
  console.log('\nffmpeg not installed — narration WAVs are ready, muxing deferred.');
  console.log('Run:  ! sudo apt-get install -y ffmpeg   then re-run this script.');
  process.exit(0);
}

// video duration
const dur = parseFloat(execFileSync('ffprobe', ['-v', 'error', '-show_entries',
  'format=duration', '-of', 'default=nw=1:nk=1', VIDEO]).toString().trim());

// soft major-triad pad as a low bed (license-clean, we generate it)
const bed = join(AUDIO, 'bed.wav');
execFileSync('ffmpeg', ['-y',
  '-f', 'lavfi', '-i', `sine=frequency=220:duration=${dur}`,
  '-f', 'lavfi', '-i', `sine=frequency=277.18:duration=${dur}`,
  '-f', 'lavfi', '-i', `sine=frequency=329.63:duration=${dur}`,
  '-filter_complex',
  '[0][1][2]amix=inputs=3:normalize=0,tremolo=f=0.12:d=0.5,' +
  'aecho=0.8:0.88:800|1200:0.25|0.18,lowpass=f=1100,' +
  `afade=t=in:st=0:d=2,afade=t=out:st=${(dur - 2).toFixed(2)}:d=2,volume=0.09[a]`,
  '-map', '[a]', bed], { stdio: 'inherit' });

// inputs:  0 = video, 1 = bed, 2+i = narration clip for timeline[i]
const narInputs = timeline.flatMap(({ step }) => ['-i', join(AUDIO, `beat-${step}.wav`)]);
const allInputs = ['-i', VIDEO, '-i', bed, ...narInputs];

const probeDur = (f) => parseFloat(execFileSync('ffprobe', ['-v', 'error',
  '-show_entries', 'format=duration', '-of', 'default=nw=1:nk=1', f]).toString().trim());

const PITCH = 1.06; // brighten the tone (higher = perkier)
const PAD_MS = 220;  // guaranteed silence before the next line starts

// For each clip: lift pitch, then time-compress just enough that it always finishes
// before the next beat's line begins — so lines never overlap.
const delayParts = timeline.map(({ step, at }, i) => {
  const clipDur = probeDur(join(AUDIO, `beat-${step}.wav`)) * 1000; // ms
  const nextAt = i < timeline.length - 1 ? timeline[i + 1].at : dur * 1000;
  const avail = Math.max(600, nextAt - at - PAD_MS);                // ms slot for this line
  const neededSpeed = clipDur / avail;                             // >1 means too long
  // asetrate already speeds by PITCH; atempo makes up any remainder, capped for quality
  const atempo = Math.min(1.8, Math.max(1.0, neededSpeed / PITCH));
  return `[${2 + i}:a]asetrate=22050*${PITCH},aresample=48000,atempo=${atempo.toFixed(3)},`
    + `adelay=${at}|${at},volume=1.4[n${i}]`;
});
const mixLabels = ['[1:a]', ...timeline.map((_, i) => `[n${i}]`)].join('');
const filterComplex = [
  ...delayParts,
  `${mixLabels}amix=inputs=${timeline.length + 1}:normalize=0:duration=first[m]`,
  '[m]alimiter=limit=0.95:level=false,aresample=48000[mixed]',
].join(';');

function mux(outFile, vcodec, acodec) {
  execFileSync('ffmpeg', ['-y', ...allInputs,
    '-filter_complex', filterComplex,
    '-map', '0:v', '-map', '[mixed]',
    '-c:v', ...vcodec, '-c:a', ...acodec, '-shortest', outFile],
    { stdio: 'inherit' });
}
// webm keeps the VP8 video as-is, opus audio
mux(join(dir, '../../public/reels/child-reel.webm'), ['copy'], ['libopus', '-b:a', '96k']);
// mp4 for Safari / iOS + sharing
mux(join(dir, '../../public/reels/child-reel.mp4'),
  ['libx264', '-pix_fmt', 'yuv420p', '-crf', '26', '-preset', 'veryfast'],
  ['aac', '-b:a', '128k']);

console.log('\nMuxed narration + music into public/reels/child-reel.webm and .mp4');
