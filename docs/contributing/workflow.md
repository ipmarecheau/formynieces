# Development Workflow

## Spec-driven, test-gated

SmoothSeas is defined by 479 Gherkin scenarios across 52 feature files (`formynieces-spec/features/`).
Each scenario passes four gates: **built → tested → verified**. Track status with:

```bash
php artisan specs:trace            # reconcile scenarios ↔ Pest tests ↔ manual verifications
php artisan specs:trace --mvp      # MVP subset
php artisan specs:verify XX-NN     # record a manual browser verification
```

The generated [Feature status](../features/index.md) page summarizes this.

## The build loop (per scenario)

1. Pick the next scenario (`specs:trace`).
2. Write a **failing** Pest test for it.
3. Write the minimum code to pass.
4. Run the affected tests, then the suite.
5. Browser-verify, commit, record the verification.

The `formynieces-bdd-loop` skill encodes this. New Laravel work should follow the `laravel-best-practices`
skill; lessons the `lesson-authoring` skill.

## Testing

```bash
./vendor/bin/pest --compact                  # full Unit+Feature suite
./vendor/bin/pest --filter=SomeTest          # one test
./vendor/bin/pest tests/Browser/...          # browser (Playwright) tests — run explicitly
vendor/bin/pint --dirty --format agent       # format before committing
```

Browser tests live in `tests/Browser/` and are **not** in the default suite (no Playwright on CI). The
CI gate runs Unit + Feature only.

## Commits & deploy

- Branch off `main`; commit only when asked. Every push to `main` runs the [CI gate then deploys](../operations/deployment.md).
- Keep the shared repo diagram current: after any repo change run `bash /root/dev/repo-status.sh`.

## Keeping these docs current

- This wiki lives in `docs/` and is part of the codebase — update it in the **same PR** as the change.
- **Generated pages** come from `php artisan docs:generate` — edit the source, not the page.
- Build/preview the site locally: `python3 -m mkdocs serve` (or `build`).
