#!/bin/bash
set -e

# All deploy output is timestamped and appended to a persistent log, so every
# deploy — and every migrate it runs — is on record (audit / incident trail).
DEPLOY_LOG=/opt/formynieces-backups/deploy.log
exec > >(while IFS= read -r line; do echo "$(date '+%Y-%m-%d %H:%M:%S') $line"; done | tee -a "$DEPLOY_LOG") 2>&1

echo "===== DEPLOY START ====="

DB_FILE=/opt/formynieces-data/db/database.sqlite

# GUARD: the production DB lives on a host volume and must NEVER be recreated or
# overwritten by a deploy. Abort if it is missing/empty so a misconfigured volume
# can never silently start the app on a fresh database.
if [ ! -s "$DB_FILE" ]; then
  echo "FATAL: production DB $DB_FILE is missing or empty — aborting deploy to avoid starting fresh."
  exit 1
fi

# Safety snapshot of the live DB before anything runs (WAL-safe), so any deploy
# is recoverable.
PRE="/opt/formynieces-backups/database_predeploy_$(date +%Y%m%d_%H%M%S).sqlite"
echo "Backing up production DB to $PRE ..."
sqlite3 "$DB_FILE" ".backup '$PRE'"

echo "Pulling latest from GitHub..."
cd /opt/formynieces
git pull origin main

echo "Copying production env..."
cp .env.production .env

echo "Building Docker image..."

# Ensure an APP_KEY exists (generate once if missing; never overwrite)
if ! grep -q '^APP_KEY=base64:' .env.production; then
  echo "No APP_KEY found — generating one..."
  KEY="base64:$(head -c 32 /dev/urandom | base64)"
  # remove any empty APP_KEY= line, then append the real key
  sed -i '/^APP_KEY=$/d' .env.production
  echo "APP_KEY=${KEY}" >> .env.production
  cp .env.production .env
  echo "APP_KEY generated and saved to .env.production"
else
  echo "APP_KEY present — skipping generation."
fi

# Build ONCE, tagged by the immutable commit SHA (+ latest). Prod and staging both run
# this exact image, so code parity is guaranteed and traceable. The SHA is baked into
# the image (version.txt) for the parity check below.
SHA=$(git rev-parse --short HEAD)
echo "Building image for commit ${SHA}..."
docker build --build-arg GIT_SHA="${SHA}" -t "formynieces:${SHA}" -t formynieces:latest .

echo "Stopping old container if running..."
docker stop formynieces 2>/dev/null || true
docker rm formynieces 2>/dev/null || true

echo "Starting new container (formynieces:${SHA})..."
docker run -d \
  --name formynieces \
  --restart unless-stopped \
  --log-opt max-size=10m \
  --log-opt max-file=3 \
  -p 127.0.0.1:8080:8080 \
  -v /opt/formynieces-data/db:/var/www/html/db \
  -v /opt/formynieces-data/storage:/var/www/html/storage \
  "formynieces:${SHA}"

echo "Waiting for container to start..."
sleep 5

echo "Running migrations (schema only — safe, additive)..."
docker exec formynieces php artisan migrate --force

# NOTE: `php artisan db:seed --force` is DELIBERATELY NOT run on deploy.
# The full DatabaseSeeder truncated syllabus_modules (and seeded test accounts),
# which cascaded and WIPED every learner's student_progress + module_stage_
# completions. Running it on production destroyed real progress three times.
# Content updates (modules/lessons/questions) are idempotent; when needed they
# are seeded MANUALLY and per-class, e.g.:
#   docker exec formynieces php artisan db:seed --class=SyllabusModuleSeeder --force
#   docker exec formynieces php artisan db:seed --class=LessonSeeder --force
# Never run the bare `db:seed` against production.

docker exec formynieces php artisan config:cache
docker exec formynieces php artisan route:cache
docker exec formynieces php artisan view:cache

# Keep the staging mirror on the SAME image as prod (code parity). Its sanitized data
# volume persists; the nightly refresh (/opt/refresh-staging.sh) handles data.
STAGING_SYNCED=false
if [ -f /opt/staging-run.sh ] && docker ps -a --format '{{.Names}}' | grep -q '^formynieces-staging$'; then
  echo "Syncing staging to formynieces:${SHA}..."
  bash /opt/staging-run.sh
  docker exec formynieces-staging php artisan migrate --force
  STAGING_SYNCED=true
fi

echo "Pruning old dangling images..."
docker image prune -f

# PARITY CHECK — assert prod (and staging, when present) actually run this commit.
# Turns silent drift into a hard deploy failure.
sleep 3
PROD_SHA=$(curl -s --max-time 10 http://127.0.0.1:8080/version -H 'Host: smoothseas.org' | grep -oE '"commit":"[^"]*"' | cut -d'"' -f4)
echo "prod /version reports: ${PROD_SHA} (expected ${SHA})"
if [ "$PROD_SHA" != "$SHA" ]; then
  echo "FATAL: prod is not running the deployed commit (${PROD_SHA} != ${SHA})."
  exit 1
fi
if [ "$STAGING_SYNCED" = true ]; then
  STAGING_SHA=$(curl -s --max-time 10 http://127.0.0.1:8090/version -H 'Host: staging.smoothseas.org' | grep -oE '"commit":"[^"]*"' | cut -d'"' -f4)
  echo "staging /version reports: ${STAGING_SHA} (expected ${SHA})"
  if [ "$STAGING_SHA" != "$SHA" ]; then
    echo "FATAL: staging mirror drifted from prod (${STAGING_SHA} != ${SHA})."
    exit 1
  fi
  echo "PARITY OK — prod and staging both on ${SHA}."
fi

echo "===== DEPLOY DONE — app running at http://172.233.163.6:8080 ====="
