# Environments

Three environments, one codebase. All on one Linode VPS behind Caddy (auto-TLS).

| Environment | URL | Port | Code | Data | Access |
|---|---|---|---|---|---|
| **Production** | `https://smoothseas.org` | 8080 | `origin/main`, Docker `formynieces` | real learner data on host volume | public |
| **Staging** | `https://staging.smoothseas.org` | 8090 | same image as prod, Docker `formynieces-staging` | sanitized copy of prod, refreshed nightly | basic-auth |
| **Dev** | `https://dev.smoothseas.org` | 8000 | working tree (`artisan serve`), changes constantly | local SQLite (test data) | basic-auth |

Basic-auth for dev/staging: `smoothseas` / `JoinDigi2025`.

## Production

- Docker container `formynieces`, built by `deploy.sh`, served by Caddy at `smoothseas.org`.
- SQLite at `/opt/formynieces-data/db/database.sqlite`, **mounted** into the container — never baked
  into the image, so rebuilds never touch data.
- `storage/` persisted at `/opt/formynieces-data/storage`.

## Staging — the production mirror

A true mirror for live diagnosis without risking real users:

- **Same image** as prod (code parity, asserted every deploy — see [Deployment](deployment.md)).
- **Own, isolated** DB volume (`/opt/formynieces-staging/db`) and env (`.env.staging`:
  `APP_ENV=staging`, `MAIL_MAILER=log`, blank `LLM_API_KEY` → no real outbound/spend).
- **Refreshed nightly** (cron `30 3 * * *` → `bash /opt/refresh-staging.sh`): copies the latest prod
  backup, migrates, then runs `php artisan staging:sanitize`.

### Sanitization

`staging:sanitize` **refuses to run unless `APP_ENV=staging`** — it can never touch production. It:

- anonymizes real accounts (`name → "Parent/Student {id}"`, `email → user{id}@staging.invalid`, phones/
  socials stripped, password → shared `staging-walkthrough`), blanks recoverable child passwords;
- clears prospect/PII tables (`leads`, `contact_messages`, chat);
- **leaves `is_test` (QA) accounts untouched** so the walkthrough agent's known credentials still work.

Staging logins: real accounts use password `staging-walkthrough`; QA accounts use their normal creds.

## Scripts

| Script | Purpose |
|---|---|
| `/opt/deploy.sh` *(repo `deploy.sh`)* | Build + deploy prod, sync staging, assert parity |
| `/opt/staging-run.sh` | (Re)create the staging container from the current prod image |
| `/opt/refresh-staging.sh` | Refresh staging data from the latest prod backup + sanitize |
| `/opt/backup-formynieces.sh` | 4-hourly WAL-safe DB backup (14-day retention) |

Backups land in `/opt/formynieces-backups/` (`database_*.sqlite`, plus `database_predeploy_*` and
`database_prerestore_*` safety snapshots).
