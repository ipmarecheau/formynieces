#!/usr/bin/env bash
# Parity gate — the single test-run that certifies a student-app parity iteration.
# Runs: Pest API tests + Flutter analyze + Flutter tests (goldens/widgets) + the parity audit.
# Exits non-zero on any test/analyze failure. With --require-complete, also fails if the
# child app is not 100% built (use in CI once the student app is meant to be finished).
#
# Usage: bash formynieces-spec/mobile/tools/parity-gate.sh [--require-complete]
set -u

ROOT="$(cd "$(dirname "$0")/../../.." && pwd)"
cd "$ROOT" || exit 2
export PATH="$PATH:/opt/flutter/bin"

fails=0
step() { echo; echo "▶ $1"; }
ok()   { echo "  ✅ $1"; }
bad()  { echo "  ❌ $1"; fails=$((fails+1)); }

step "Pest — mobile API (behaviour + contract)"
if ./vendor/bin/pest tests/Feature/Api --compact >/tmp/parity_pest.out 2>&1; then
  ok "$(tail -1 /tmp/parity_pest.out)"
else
  bad "API tests failed"; tail -8 /tmp/parity_pest.out
fi

step "Flutter analyze — child app"
if (cd mobile/smoothseas_child && flutter analyze >/tmp/parity_an.out 2>&1); then
  ok "no analyzer issues"
else
  grep -E "error •|info •|warning •" /tmp/parity_an.out | tail -8; bad "analyzer issues"
fi

step "Flutter test — child app (flow + golden)"
if (cd mobile/smoothseas_child && flutter test >/tmp/parity_ft.out 2>&1); then
  ok "$(grep -E 'All tests passed|tests passed' /tmp/parity_ft.out | tail -1)"
else
  tail -12 /tmp/parity_ft.out; bad "flutter tests failed"
fi

step "Parity audit — completeness"
php artisan mobile:parity-audit >/tmp/parity_audit.out 2>&1
child_summary="$(grep 'SUMMARY child:' /tmp/parity_audit.out | tail -1)"
echo "  ${child_summary:-SUMMARY child: (not found)}"
built="$(echo "$child_summary" | grep -oE '[0-9]+/[0-9]+' | head -1)"
child_done=0
[ "${built%%/*}" = "${built##*/}" ] && child_done=1

echo
if [ "$fails" -gt 0 ]; then
  echo "GATE: ❌ FAIL ($fails check(s) failed)"
  exit 1
fi
if [ "${1:-}" = "--require-complete" ] && [ "$child_done" -ne 1 ]; then
  echo "GATE: ❌ child app not complete ($built) — --require-complete set"
  exit 1
fi
echo "GATE: ✅ PASS  (child parity: ${built:-?})"
