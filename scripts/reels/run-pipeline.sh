#!/bin/bash
set -e
cd /root/dev/formynieces
SP=/tmp/claude-0/-root-dev-formynieces/94033ef5-0d34-4bf7-800c-4a9632257575/scratchpad
export PATH="$SP/ffmpeg-static:$PATH"
RAW=$(ls -t scripts/reels/out/page@*.webm | head -1)
echo "encode base from $RAW"
ffmpeg -y -itsscale 0.52 -i "$RAW" -an -c:v libvpx -b:v 900k -deadline good -cpu-used 2 scripts/reels/out/child-reel-base.webm 2>/dev/null
echo "base done"
node scripts/reels/build-audio.mjs 2>&1 | grep -iE "Synthesized|Muxed"
ffmpeg -y -ss 12.5 -i public/reels/child-reel.mp4 -frames:v 1 public/reels/child-reel-poster.png 2>/dev/null
node scripts/reels/build-preview.mjs
echo "PIPELINE DONE"
