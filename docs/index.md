# SmoothSeas — Engineering Wiki

SmoothSeas is an SEA-exam preparation platform for primary-school students in Trinidad & Tobago.
A child works a daily **learning loop** (lesson → practice → mastery) across a gamified **Voyage**
map; a guardian sees an honest **progress dashboard**. The teaching engine is deterministic PHP;
AI is used only at the edges (essay scoring, re-teach chat, OCR), always behind budget caps.

This wiki is the **single source of truth** for how the system is built and operated. It lives in
the repository (`docs/`), is reviewed in the same pull requests as the code it describes, and is
published as a static site with MkDocs. Plain Markdown keeps it crawlable by both people and LLMs.

## Start here

| If you want to… | Read |
|---|---|
| Understand the whole system at a glance | [Architecture overview](architecture/overview.md) (C4) |
| Understand the core teaching engine | [The learning loop](architecture/learning-loop.md) |
| See the data model | [Data model](architecture/data-model.md) |
| Deploy, roll back, or understand CI | [Deployment & CI](operations/deployment.md) |
| Know the prod / staging / dev layout | [Environments](operations/environments.md) |
| Fix a production incident | [Runbooks](operations/runbooks.md) |
| Drive the QA walkthrough agent | [QA agent surface](operations/qa-agent.md) |
| See what's built vs. planned | [Feature status](features/index.md) (generated) |
| Contribute code | [Development workflow](contributing/workflow.md) |

## Conventions

- **Current-state only.** This wiki describes what *is*, not history. Dated handoffs and superseded
  notes live in [`archive/`](archive/index.md) and are not maintained.
- **Generated pages** (feature status, routes, data model) are produced by `php artisan docs:generate`
  and must not be hand-edited — edit the source (specs, routes, migrations) instead.
- **The deep specification** — 52 Gherkin feature files and the numbered product spec — remains in
  `formynieces-spec/`. This wiki links into it rather than duplicating it.
