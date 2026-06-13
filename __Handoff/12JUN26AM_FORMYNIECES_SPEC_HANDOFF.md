# ForMyNieces — Specification Suite Handoff

**Date:** 12 June 2026
**Version:** 1.0
**Status:** Spec suite v1.0 complete and Gherkin-validated. Ready to commit to repo.
**Prepared by:** Spec session with Claude (Anthropic)

---

## 1. Context

Following the deployment handoff (11 June) and the onboarding/diagnostic build
handoff (Slices 1–4), this session stepped back from code to produce a formal
product specification. Goal: a spec that guides the build top-down — from the
product goal through user journeys and cadence narratives down to executable
Gherkin — with MVP/roadmap prioritisation baked in.

This suite is now the **spec of record**. Prior handoffs remain the
deployment/technical record. Where they conflict, this suite wins on product
behaviour; the handoffs win on infrastructure facts.

---

## 2. Methodology adopted

A strict derivation chain, where each layer is derived from the one above:

```
GOAL → USER JOURNEYS → CADENCE NARRATIVES (weekly/daily)
     → OBJECT MODEL (OOUX) → SCREENS + NAVIGATION → GHERKIN → ROADMAP
```

Named methods underpinning each step:
- **Impact Mapping / User Story Mapping (Patton)** — goal and journey backbones.
- **Day-in-the-life cadence narratives** — the weekly/daily decomposition; the
  unit of design for a cadence product.
- **Object-Oriented UX (OOUX)** — nouns → screens (collection + detail),
  verbs → CTAs/flows, object relationships → drill-down navigation.
- **Frequency rule for navigation** — daily-narrative objects go in persistent
  nav/dashboard; weekly objects one tap deep; rarer goes behind menus/flows.
- **Specification by Example / BDD (Gherkin)** — only behaviour with
  consequences gets scenarios; plain CRUD does not.

Consistency rules (enforced manually for now):
1. Every Gherkin `When` must be reachable from a screen in doc 03.
2. Every Gherkin `Then … sees` must have a home on a screen in doc 03.
3. Every screen must be justified by ≥1 scenario.
4. Every object must appear in ≥1 narrative.

---

## 3. Deliverables (in `formynieces-spec.zip`)

| File | Contents |
|---|---|
| `00_SPEC_OVERVIEW.md` | Goal, actors, settled design constraints, derivation chain, priority scheme, out-of-scope list |
| `01_USER_JOURNEYS.md` | Student + guardian backbones; Tuesday/Saturday/Sunday narratives; anti-narratives; the eight scenarios S1–S8 |
| `02_OBJECT_MODEL.md` | OOUX objects mapped to existing schema (✅/🔧/🆕 flags); relationship sketch; schema deltas beyond Slice 1 |
| `03_SCREENS_AND_NAVIGATION.md` | Screen inventory (A auth, B onboarding, C student, D guardian, E admin) with priorities + routes; Mermaid navigation map; routing guards; navigation principles |
| `features/*.feature` (11 files) | Executable Gherkin, priority-tagged |
| `04_FEATURE_INDEX.md` | Feature ↔ priority table; S1–S8 coverage matrix; Pest-vs-Behat recommendation |
| `ROADMAP.md` | Phases 0–4 against the SEA 2027 calendar; standing risks |

---

## 4. Key decisions made this session

### Prioritisation = Gherkin tags
`@mvp`, `@v1.1`, `@roadmap` at feature level with per-scenario overrides, plus
actor tags (`@student`, `@guardian`, `@admin`, `@system`) and household-scenario
tags (`@scenario-S1` … `@scenario-S8`). Spec, test suite, and roadmap share one
vocabulary; the MVP test suite is literally `--tags @mvp`.

### MVP definition (one sentence)
One real student can be onboarded by her guardian, take the diagnostic, receive
a personalised 30-week roadmap, complete weekly targets through the learning
loop, submit writing for AI feedback, and her guardian can see an honest
picture — **on a database that survives redeploys**.

### Scenario coverage
S1–S5 land in MVP; S6 (disrupted → pause/resume) and S7 (disengaged guardian →
digest) in v1.1; S8 (conflicted household → read-only second guardian) on the
roadmap.

### Schema deltas the spec implies (beyond Slice 1)
| Change | Priority |
|---|---|
| `writing_submissions` table (steady-state track; diagnostic sample stays on `diagnostic_sessions`) | @mvp |
| `student_progress.status` += `in_review` (semantics to confirm) | @mvp |
| student `target_sea_year` | @mvp |
| guardian phone verification fields | @v1.1 |
| pause/resume fields | @v1.1 |
| second-guardian link (read-only) | @roadmap |
| notifications/digest log | @v1.1 |

### Navigation principles locked
Student persistent nav = exactly two items (Home, Map); no hamburger for a
10-year-old. Guardian home = one screen answering the four Sunday questions.
Onboarding is a rail (resumable, no nav escape). Two-layer model enforced at
the routing level: agent content never renders on student routes.

### Tooling recommendation
Keep `.feature` files as spec of record; implement with **Pest**, naming tests
1:1 with scenario titles (`it('rolls misses forward with a cap')`). Behat only
if verbatim execution of the `.feature` files is wanted later.

---

## 5. Gherkin quality pass (important)

The first draft of the feature files violated Gherkin structure (Then-only
scenarios, invariants written as scenarios, multiple When→Then cycles per
scenario, misplaced `But`, mixed first/third person). All 11 files were
rewritten:

- Strict `Given* → When+ → Then+` ordering in every scenario.
- Invariants moved into `Rule:` blocks (Gherkin 6+) with concrete illustrating
  scenarios (map kindness, test-free diagnostic framing, read-only second
  guardian, shame-free pause/resume, no motivational styling in guardian data).
- One behaviour per scenario; multi-step flows split.
- Consistent third-person declarative voice for reusable step definitions.

**Validated** with the official parser (`pip install gherkin-official`) plus a
structural lint asserting every scenario matches `G*W+T+` after resolving
And/But. All 11 files pass.

### Recommended CI step (add when committing specs to repo)
Place files at `specs/` (docs) and `specs/features/` (Gherkin), then:

```yaml
# .github/workflows/spec-lint.yml (or a job in deploy.yml)
- run: pip install gherkin-official
- run: python scripts/lint_gherkin.py specs/features
```

(The lint script from this session: parse each file, resolve And/But to the
previous keyword, fail unless step sequence matches `G*W+T+` and every
scenario has ≥1 When and ≥1 Then.)

---

## 6. Roadmap summary (see ROADMAP.md for detail)

| Phase | Window | Theme | Hard gates |
|---|---|---|---|
| 0 | now → end June 2026 | Stabilise: fix job_batches blocker, **DB persistence/backup**, security updates, domain+HTTPS, GitHub Actions, apply Slice 1 | No real child data before DB persistence + HTTPS |
| 1 | July 2026 | MVP: Slices 2–4, writing track, rollover job, guardian dashboard, streaks → pilot with the nieces for 2 full weeks | All `@mvp` scenarios pass |
| 2 | Aug 2026 | v1.1: author Writing modules, digest, pause/resume, retake, decay, Filament anchor UI, phone verify | |
| 3 | Sep–Dec 2026 | Exam readiness: fill-in Math input, timed mocks, ELA Section II depth, adaptive v2 | New-content learning must start by ~late Sep for full 24+6 weeks |
| 4 | Jan–May 2027 | Revision buffer mode, exam-week state, post-exam summary, S8, scale decision | |

Anchor: SEA 2027 expected late April/early May 2027 — **confirm exact date when
MoE publishes it** and recompute the buffer.

---

## 7. Open items

- [ ] Commit the spec suite to the repo (suggested: `specs/` + `specs/features/`).
- [ ] Add the Gherkin lint to CI.
- [ ] Confirm `in_review` status semantics (decay rules, who sets it).
- [ ] Confirm weekly rollover cap value (spec suggests max 6 modules/week).
- [ ] Confirm mastery threshold for module quizzes (not yet specified anywhere).
- [ ] Design the `writing_submissions` migration (Phase 1).
- [ ] Cross-check doc 03 screen inventory against the 21-screen sitemap from the
      09 June session; reconcile deltas (B7 resume added; C8/D4–D7 deferred).
- [ ] Everything in ROADMAP Phase 0 — still blocked on the SQLite `job_batches`
      clean rebuild from the 11 June handoff.

---

## 8. Reference files

- This session: `formynieces-spec.zip` (6 docs + 11 feature files).
- Prior handoffs: `11JUN26PM_FORMYNIECES_DEPLOYMENT_HANDOFF.md` (onboarding/
  diagnostic slices), `11JUN26_FORMYNIECES_DEPLOYMENT_HANDOFF.md` (Docker/VPS),
  `09JUN26_FORMYNIECES_DASHBOARD_HANDOFF.md` (dashboard UX, eight scenarios,
  sitemap), `08JUN26_FORMY_NIECES_HANDOFF.md` (Laravel/backend).
