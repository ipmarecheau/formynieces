# QA Agent Surface

A token-gated surface on production that lets an autonomous walkthrough agent (OpenAI desktop control)
understand the product, walk the user stories, and report findings. All routes are off unless
`QA_ACCESS_TOKEN` is set in the environment.

## Endpoints

| Endpoint | Purpose |
|---|---|
| `GET /qa/manifest?token=…` | Features + user stories parsed live from the Gherkin specs, plus "how to test". JSON; add `&format=html` for a reading view. |
| `GET /qa/reports?token=…` | Dashboard of findings, newest first, by outcome. |
| `POST /qa/report` | The report inbox. Token via `?token=` or `X-QA-Token` header (CSRF-exempt). |

Report body:

```json
{ "scenario": "LL-20", "outcome": "pass|fail|blocked|gap|note",
  "summary": "…", "detail": "…", "actor": "qa-agent", "screenshot_url": "…" }
```

## Recommended loop

The agent's orchestration layer reads `/qa/manifest`, drives the UI as the QA accounts, then `POST`s one
report per scenario. This beats a file drop (FTP): structured, queryable, timestamped, one home.

## Accounts

Created by `php artisan qa:seed-accounts --password=…` (idempotent, all flagged `is_test`):

- Guardian: `qa-parent@qa.smoothseas.org`
- Children: `qa-child-low` / `qa-child-mid` / `qa-child-high@qa.smoothseas.org` (reading levels 4/5/6)

Because `is_test` accounts are excluded from analytics (`User::real()`) and skipped by the staging
sanitizer, they are safe to run daily and work on both prod and staging.

## Guardrails

- **The agent acts through the UI only — never the database.**
- **Real LLM cost:** essays and guardian summaries hit the live API to a per-student monthly cap.
- The agent **consumes finite no-repeat content** for its own accounts over time (expected).

The token lives only in the environment; rotate by changing `QA_ACCESS_TOKEN` and redeploying.
