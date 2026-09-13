# Student-App Parity Loop

A repeatable, **test-gated** cycle that drives the Flutter student app to *exact* parity with
the web student experience — one surface at a time, with parity proven by tests, not eyeballs.

Driven by `parity.yml` (the surface inventory) and certified by `tools/parity-gate.sh`
(the single command that runs every parity check). A surface is **done only when the gate is
green and its `mobile:parity-audit` row is ✅**.

## The four parity layers (each has a test framework that enforces it)

| Layer | Question | Enforced by |
|---|---|---|
| **Behaviour** | Does the API obey the same rules as the web? | **Pest** API tests that mirror the web Gherkin scenario's Then-clauses, over the **same domain services** (never reimplemented). |
| **Contract** | Does the JSON shape match what Flutter expects? | Pest `assertJsonStructure` / schema assertions; Dart models mapped to the same shape. |
| **Flow** | Do the screen's states match the web (loaded / empty / error / gated)? | **Flutter widget tests** with an injected `MockClient` (`ApiClient(client:)`). |
| **Visual** | Does it look right, and stay that way? | **Flutter golden tests** (headless), with the web screenshot from `parity-capture` as the human reference. |

## The loop (per surface)

1. **SELECT** — `php artisan mobile:parity-audit --only-gaps`; take the next `child:` row.
2. **CAPTURE** — `tools/parity-capture.mjs` → web screenshots of every state; note the web
   scenario id(s) from `formynieces-spec/features/*.feature`.
3. **EXTRACT** — read the backend service + the Gherkin scenario; write the rules/states down
   as acceptance criteria (backend is the source of truth, never guessed).
4. **RED** — write the failing tests first: Pest API tests (one per Then-clause) + a Flutter
   widget test per state + a golden placeholder.
5. **GREEN** — build the API endpoint (reuse the service) and the Flutter screen until all pass;
   `flutter test --update-goldens` to baseline the look.
6. **GATE** — `tools/parity-gate.sh` must be green (all Pest + analyze + flutter test + audit).
7. **RECORD + COMMIT** — the audit row flips ✅; commit the slice; push.
8. **REPEAT** — until the gate reports **child parity = 100%**.

## Definition of done (per surface)
- Pest API tests green (behaviour + contract).
- Flutter widget tests green (every state).
- Golden baselined and passing (visual).
- `parity.yml` row present and `mobile:parity-audit` shows ✅.
- `parity-gate.sh` exits 0.

## Running the gate
```
bash formynieces-spec/mobile/tools/parity-gate.sh          # full gate
bash formynieces-spec/mobile/tools/parity-gate.sh --child  # student app only
```
It fails (non-zero) on any test failure or any child surface not fully built — so CI can block
merges that regress or leave a student surface incomplete.
