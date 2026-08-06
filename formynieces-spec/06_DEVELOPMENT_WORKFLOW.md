# 06 — Development Workflow (Document-First · BDD · TDD)

**Status:** Living document — the standing process for building any feature in this
project. It sits alongside the derivation chain in `00_SPEC_OVERVIEW.md`: that chain
says *what* the product is; this document says *how* a change gets built and proven.

The one rule: **a feature is not "done" until a user story, a Gherkin scenario, a
passing test, a browser verification, and a ledger entry all exist for it.** Code
without those is a draft, not a delivery.

---

## 1. Why this workflow

- **Traceability both ways.** Any line of code traces up to the scenario, story, and
  goal that justify it; any goal traces down to the scenarios and tests that fulfil it.
- **Always shippable.** The full test suite stays green at every step, so `main` is
  always a candidate for release.
- **Honest verification.** "It works" means a human confirmed it in the running app —
  not that a test the author wrote passed. The two are recorded separately.

---

## 2. The pipeline

Every feature flows through these stages in order. Earlier stages are *documents*;
code does not begin until the behaviour is written down.

| Stage | Name | Artifact | Where |
|---|---|---|---|
| 0 | **User Story** | A persona-voiced narrative, banded by release (`@mvp`/`@v1.1`/`@roadmap`) | `05_USER_STORIES_BY_RELEASE.md` |
| 1 | **Document-First** | The feature written up *before* code: a `.feature` file, plus its row in the feature index | `features/*.feature`, `04_FEATURE_INDEX.md` |
| 2 | **BDD** | Each consequential behaviour as a Gherkin scenario with a `@scenario:XX-NN` id | `features/*.feature` |
| 3 | **TDD** | A failing Pest test per scenario (`->group('scenario:XX-NN')`), then the minimum code to pass | `tests/**`, `app/**` |
| 4 | **Verification** | The owner confirms the behaviour in the running app; recorded with his words | `verifications.yml` via `specs:verify` |
| 5 | **Commit & trace** | A commit with `Executed-By:` attribution; status visible in the tracer | `git`, `specs:trace` |

### Stage 0 — User Story
Start in the persona's voice ("As Maya… / As the admin…"), not in the implementation.
Add or extend the story in `05` and band it (`@mvp` / `@v1.1` / `@roadmap`). If a change
has no home in a persona's story, question whether it belongs.

### Stage 1 — Document-First
Write the behaviour down **before** implementing it. Create or extend the Gherkin
`.feature` file and add the feature to `04_FEATURE_INDEX.md`. This is the point of most
leverage: getting the words right is cheaper than getting the code wrong.

### Stage 2 — BDD
Express behaviour as `Given / When / Then`, grouped under `Rule:` blocks, each tagged
`@scenario:XX-NN` with a unique two-letter feature prefix (e.g. `QB`, `AM`, `VC`).
**Only behaviour with consequences becomes a scenario** — adaptive logic, guards,
separations, formats, validations. Plain CRUD does not; keep the spec maintenance-light.

### Stage 3 — TDD
For each scenario: write the failing Pest test first, tagged
`->group('scenario:XX-NN')` so it traces 1:1 to the scenario. Then write the **minimum**
code to make it pass. Red → green → refactor, and keep the whole suite green. Format with
Pint before finishing.

### Stage 4 — Verification (never skipped, never self-served)
Tests prove the author's intent; **verification proves it works for a human.** The owner
(Isaac) exercises the feature in the running app, then it is recorded with
`php artisan specs:verify XX-NN --note="<what he actually confirmed>"`. The note reflects
his real words. A scenario is **never** marked verified from test/tinker evidence alone.

### Stage 5 — Commit & trace
Commit per feature or per scenario with an `Executed-By:` trailer (who wrote the code).
`php artisan specs:trace` then shows, for every scenario, whether it has a test and a
verification — the single source of truth for "what's actually done."

---

## 3. Definition of done (checklist)

- [ ] The change appears in a persona's user story (`05`), correctly banded.
- [ ] A `.feature` scenario (`@scenario:XX-NN`) describes each consequential behaviour.
- [ ] A Pest test per scenario passes, grouped `scenario:XX-NN`; full suite green; Pint clean.
- [ ] The owner has verified it in the running app; `specs:verify` recorded his confirmation.
- [ ] Committed with `Executed-By:`; `specs:trace` shows it green and verified.

---

## 4. Roles

- **Orchestrator (Claude, this session).** Picks the scenario, writes the tests (never
  delegated), runs the suite, drives commits, verification, and the ledger.
- **GLM executor (optional).** May write *minimum implementation code* for a scenario
  after approval; never touches tests, git, migrations, or verification. See the project
  `CLAUDE.md` for delegation and gate rules.

The gates in `CLAUDE.md` (scenario selection, delegation, accepting work, commit,
`specs:verify`, push, destructive actions) govern *when* the orchestrator may act; this
document governs the *shape* of the work between the gates.

---

## 5. Worked example — the Question Bank

The admin question-bank tooling followed this pipeline (and was reworked when the first
pass skipped Stage 1):

1. **Story:** the admin persona curates the bank — importing, authoring, exporting (`05`).
2. **Document-First:** `features/question_bank.feature` + a row in `04`.
3. **BDD:** `QB-01…10` across three rules (safe/idempotent import, hand authoring, XML export).
4. **TDD:** Pest tests grouped `scenario:QB-01…10`; full suite green.
5. **Verification:** pending — the owner exercises import/authoring/export in `/admin`,
   then `specs:verify QB-0x` per scenario.

> Lesson recorded here on purpose: the importer was first built with tests but **no
> `.feature` file** — Stage 1 was skipped. Document-first is not optional; the spec is
> the contract the tests and code answer to.
