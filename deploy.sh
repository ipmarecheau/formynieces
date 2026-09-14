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

docker build -t formynieces:latest .

echo "Stopping old container if running..."
docker stop formynieces 2>/dev/null || true
docker rm formynieces 2>/dev/null || true

echo "Starting new container..."
docker run -d \
  --name formynieces \
  --restart unless-stopped \
  --log-opt max-size=10m \
  --log-opt max-file=3 \
  -p 127.0.0.1:8080:8080 \
  -v /opt/formynieces-data/db:/var/www/html/db \
  -v /opt/formynieces-data/storage:/var/www/html/storage \
  formynieces:latest

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

echo "Pruning old dangling images..."
docker image prune -f

echo "===== DEPLOY DONE — app running at http://172.233.163.6:8080 ====="
