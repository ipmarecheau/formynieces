# Persona-simulation agent

Every night a small agent plays two stand-in users through the live app and records what they
hit, so the team catches friction before a real family does:

- **Anxious parent** — signs in, hunts for reassurance, checks progress and billing.
- **Child's full day** — completes a full day's quota: Captain's Orders + island mastery.

Each run's findings are reviewed and **critiqued** in the admin panel. The agent has its own
**separate monthly USD budget** (default **$5**, `PERSONA_AGENT_MONTHLY_CAP_USD`) and skips
runs once that is reached — it can never run up a surprise bill.

Specified by **`persona_agent.feature`** (`PA-01`–`PA-06`).

!!! note "Status"
    **Framework built and tested.** The live **engine** (`BrowserPersonaSimulator` — Playwright
    against staging + an LLM that reasons and writes findings) is the deferred piece; until it
    is wired it throws and the run is recorded as *failed*. The test suite binds a
    `FakePersonaSimulator`, so schedule/budget/recording/admin are all proven offline.

## How it runs

- **`personas:run`** (scheduled nightly at 02:30) → `PersonaTestingService::runNightly()`.
- For each persona: if `PersonaBudget::canRun()`, drive it through `PersonaSimulator::run()`
  and record a `persona_runs` row (status, summary, cost) with its `persona_findings`;
  otherwise record a `skipped_budget` run. A thrown engine error is caught and recorded as a
  `failed` run — one persona never stops the other (`PA-06`).

## Budget (`PA-02`)

`PersonaBudget` sums this month's `persona_runs.cost_usd` against the cap. It is **separate**
from the per-student LLM caps (`LlmBudget`), so persona spend never eats a child's AI budget.

## Admin review

| Resource | Purpose |
| --- | --- |
| **Persona runs** (`Quality` group) | the run log: persona, status, findings count, cost, started — read-only |
| **Persona findings** (`Quality` group) | every finding with severity/area/observation/suggestion; **Critique** action sets a verdict (valid / invalid / won't fix / fixed) + notes; nav badge counts unreviewed |

## The live engine (to implement)

`BrowserPersonaSimulator::run($persona)` is where the real work goes:

1. Drive Playwright (`pestphp/pest-plugin-browser`) against
   `services.persona_agent.base_url` (staging), as that persona, through its journey.
2. Feed what it sees to an LLM to reason about the experience and emit findings
   (`severity`, `area`, `observation`, `suggestion`), metering spend.
3. Return a `PersonaResult` (summary, cost, findings). Everything downstream already exists.

## Tests

`tests/Feature/PersonaAgentTest.php` — recording, both personas, budget skip, budget maths,
failed-walk isolation, the nightly command, and admin critique. Tagged `@group scenario:PA-0x`.
