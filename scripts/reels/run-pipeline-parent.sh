#!/bin/bash
set -e
cd /root/dev/formynieces
SP=/tmp/claude-0/-root-dev-formynieces/94033ef5-0d34-4bf7-800c-4a9632257575/scratchpad
export PATH="$SP/ffmpeg-static:$PATH"
RAW=$(ls -t scripts/reels/out/parent/page@*.webm | head -1)
echo "encode parent base from $RAW"
ffmpeg -y -itsscale 0.66 -i "$RAW" -an -c:v libvpx -b:v 900k -deadline good -cpu-used 2 scripts/reels/out/parent/parent-reel-base.webm 2>/dev/null
echo "base done"
REEL=parent node scripts/reels/build-audio.mjs 2>&1 | grep -iE "Synthesized|Muxed"
ffmpeg -y -ss 8 -i public/reels/parent-reel.mp4 -frames:v 1 public/reels/parent-reel-poster.png 2>/dev/null
echo "PARENT PIPELINE DONE"
