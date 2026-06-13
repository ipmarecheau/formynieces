# ForMyNieces — Deployment Handoff
**Date:** 11 June 2026  
**Version:** 1.0  
**Status:** In progress — blocked on SQLite migration conflict  
**Prepared by:** Deployment session with Claude (Anthropic)

---

## 1. Server details

| Property | Value |
|---|---|
| Provider | Akamai Connected Cloud (Linode) |
| IP | 172.233.163.6 |
| OS | Ubuntu 22.04.5 LTS (Jammy Jellyfish) |
| SSH user | root |
| App port | 8080 |
| App URL | http://172.233.163.6:8080 |

**Existing services on the server (do not touch):**

| Service | Port | Process |
|---|---|---|
| Kavita | 5000 | Docker container |
| Calibre content server | 8083 | `calibre-server.service` |
| Calibre-Web | 8081 | `cps.service` |

---

## 2. Deployment stack

```
GitHub (ipmarecheau/formynieces)
    ↓ git pull
Linode VPS
    └── Docker container (formynieces)
            ├── PHP 8.3-FPM (Alpine)
            ├── Nginx (port 8080)
            ├── Supervisor (process manager)
            └── SQLite (database inside container)
```

**What is NOT on the server natively:**
- No Nginx installed on host
- No PHP installed on host
- No Composer installed on host
- No Node/npm installed on host

Everything runs inside the Docker container.

---

## 3. Repository

| Property | Value |
|---|---|
| GitHub URL | https://github.com/ipmarecheau/formynieces |
| Branch | `main` |
| Visibility | Private |
| Local path (Windows) | `C:\Users\isaac\Herd\ForMyNieces` |

**Key files added during this session:**

```
Dockerfile                          — Docker image definition
deploy.sh                           — Deployment script (run on VPS)
docker/nginx.conf                   — Nginx config (port 8080)
docker/supervisord.conf             — Supervisor config (nginx + php-fpm + queue worker)
.env.production                     — Production env file (NOT committed to git)
__Handoff/08JUN26_FORMY_NIECES_HANDOFF.md
__Handoff/09JUN26_FORMYNIECES_DASHBOARD_HANDOFF.md
```

---

## 4. Current file contents

### `Dockerfile` (latest working version)

```dockerfile
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    nginx \
    nodejs \
    npm \
    supervisor \
    sqlite \
    sqlite-dev \
    icu-dev \
    libzip-dev \
    git \
    curl \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_sqlite bcmath intl zip \
    && docker-php-ext-enable pdo_sqlite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader \
    && npm install \
    && npm run build \
    && mkdir -p database \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache database

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
```

**Key change from previous version:** `touch database/database.sqlite` removed from build step — SQLite file must NOT be created during build. It is created fresh at deploy time by `php artisan migrate`.

### `deploy.sh` (latest working version)

```bash
#!/bin/bash
set -e

echo "Pulling latest from GitHub..."
cd /opt/formynieces
git pull origin main

echo "Copying production env..."
cp .env.production .env

echo "Building Docker image..."
docker build -t formynieces:latest .

echo "Stopping old container if running..."
docker stop formynieces 2>/dev/null || true
docker rm formynieces 2>/dev/null || true

echo "Starting new container..."
docker run -d \
  --name formynieces \
  --restart unless-stopped \
  -p 8080:8080 \
  -v /opt/formynieces-data/storage:/var/www/html/storage \
  formynieces:latest

echo "Waiting for container to start..."
sleep 5

echo "Running migrations..."
docker exec formynieces php artisan migrate --force
docker exec formynieces php artisan db:seed --force
docker exec formynieces php artisan config:cache
docker exec formynieces php artisan route:cache
docker exec formynieces php artisan view:cache

echo "Done. App running at http://172.233.163.6:8080"
```

**Key notes:**
- Database volume mount was removed — SQLite lives inside the container
- Storage volume IS mounted — persists logs and uploaded files across deploys
- `sleep 5` gives the container time to start before artisan commands run
- `2>/dev/null || true` on stop/rm means it won't fail if container doesn't exist

### `docker/nginx.conf`

```nginx
worker_processes 1;
events { worker_connections 1024; }

http {
    include mime.types;
    default_type application/octet-stream;
    sendfile on;

    server {
        listen 8080;
        root /var/www/html/public;
        index index.php;
        charset utf-8;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }

        location ~ /\.(?!well-known).* {
            deny all;
        }
    }
}
```

### `docker/supervisord.conf`

```ini
[supervisord]
nodaemon=true
logfile=/var/www/html/storage/logs/supervisord.log

[supervisorctl]
serverurl=unix:///tmp/supervisor.sock

[unix_http_server]
file=/tmp/supervisor.sock

[rpcinterface:supervisor]
supervisor.rpcinterface_factory = supervisor.rpcinterface:make_main_rpcinterface

[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:queue-worker]
command=php /var/www/html/artisan queue:work --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
stdout_logfile=/var/www/html/storage/logs/worker.log
stderr_logfile=/var/www/html/storage/logs/worker-error.log
```

### `.env.production` (on VPS only — never commit to git)

```env
APP_NAME=ForMyNieces
APP_ENV=production
APP_DEBUG=false
APP_URL=http://172.233.163.6:8080

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite

GROQ_API_KEY=gsk_your_key_here
GROQ_MODEL=llama-3.3-70b-versatile

QUEUE_CONNECTION=database
SESSION_DRIVER=file
CACHE_STORE=file
LOG_CHANNEL=stack
LOG_LEVEL=error
```

---

## 5. AI integration — Groq

The platform uses Groq API (free tier) instead of Anthropic for AI features.

| Property | Value |
|---|---|
| Provider | Groq |
| Console | https://console.groq.com |
| Model | `llama-3.3-70b-versatile` |
| API key prefix | `gsk_...` |
| Key storage | `.env.production` on VPS only |

**Files added for Groq integration:**
- `app/Services/GroqService.php` — HTTP client wrapper
- `config/services.php` — added `groq` key using `env('GROQ_API_KEY')`
- `app/Services/ExamAgentService.php` — `generateSummary()` and `generateWritingFeedback()` methods

**Important:** The Groq API key was accidentally committed to GitHub once during this session (in `config/services.php`). It was caught by GitHub secret scanning, the key was rotated immediately, and the commit was rewritten with `git commit --amend` + `git push --force`. The current repo is clean — `config/services.php` uses `env('GROQ_API_KEY')` with no hardcoded value.

**Groq free tier limits:**
- 30 requests/minute
- 14,400 requests/day
- 6,000 tokens/minute
- 500,000 tokens/day

---

## 6. Current blocker — SQLite job_batches conflict

### Symptom
Every deploy fails with:
```
SQLSTATE[HY000]: General error: 1 table "job_batches" already exists
```

### Root cause
The `job_batches` migration (`2026_06_12_003756_create_job_batches_table.php`) was created manually during troubleshooting and added to the repo. However, the table was also created manually inside the container during earlier debugging. When the deploy runs `php artisan migrate --force`, it tries to create the table again and fails.

### What has been tried
1. Wiping `/opt/formynieces-data/database/database.sqlite` on the host — did not help because the database was moved inside the container
2. Running `docker exec formynieces php artisan tinker` to mark migration as run — partially worked but table structure was wrong
3. Removing database volume mount entirely — correct direction but build step was still creating SQLite file with old data baked in
4. Removing `touch database/database.sqlite` from Dockerfile build step — **this is the correct fix, not yet confirmed working**

### Next step to resolve

On local machine, confirm `Dockerfile` does NOT have `touch database/database.sqlite` in the build step. Then run a full clean deploy on the VPS:

```bash
cd /opt/formynieces
git fetch origin && git reset --hard origin/main
docker stop formynieces 2>/dev/null || true
docker rm formynieces 2>/dev/null || true
docker rmi formynieces:latest 2>/dev/null || true
chmod +x deploy.sh && ./deploy.sh
```

The `docker rmi` is critical — it forces Docker to rebuild the image from scratch with no cached layers, ensuring no old SQLite data is baked into the image.

---

## 7. Migrations — current state

These are the migrations in the repo and their expected status after a clean deploy:

| Migration | Status |
|---|---|
| `0001_01_01_000000_create_users_table` | Should run |
| `0001_01_01_000001_create_cache_table` | Should run |
| `0001_01_01_000002_create_jobs_table` | Should run |
| `2026_06_05_174838_add_role_and_parent_to_users_table` | Should run |
| `2026_06_05_175105_create_syllabus_modules_table` | Should run |
| `2026_06_05_175215_create_student_progress_table` | Should run |
| `2026_06_05_175251_create_weekly_targets_table` | Should run |
| `2026_06_05_193237_add_pacing_week_to_syllabus_modules_table` | Should run |
| `2026_06_05_202938_add_description_and_resources_to_syllabus_modules_table` | Should run |
| `2026_06_05_202946_add_previous_score_to_student_progress_table` | Should run |
| `2026_06_12_003756_create_job_batches_table` | Should run — this is the blocker |

---

## 8. VPS directory structure

```
/opt/formynieces/               — Git repo (cloned from GitHub)
    Dockerfile
    deploy.sh
    .env.production             — Never committed, manually created on server
    docker/
        nginx.conf
        supervisord.conf
    ...all Laravel files...

/opt/formynieces-data/          — Persistent data (survives container rebuilds)
    storage/
        logs/
        app/
        framework/
            sessions/
            views/
            cache/
```

**Note:** `/opt/formynieces-data/database/` directory exists on the host but is no longer mounted into the container. The SQLite database now lives entirely inside the container at `/var/www/html/database/database.sqlite`. This means the database is wiped on every redeploy — acceptable for development, needs a backup strategy before real students use the platform.

---

## 9. Ongoing deploy workflow (once blocker is resolved)

### From local machine (VS Code)
```bash
# Make changes, then:
git add .
git commit -m "your message"
git push origin main
```

### On the VPS
```bash
cd /opt/formynieces
chmod +x deploy.sh && ./deploy.sh
```

### Useful VPS diagnostic commands
```bash
# Check container is running
docker ps

# Check logs
docker logs formynieces --tail 50

# Check Laravel logs
docker exec formynieces cat storage/logs/laravel.log | tail -50

# Check migration status
docker exec formynieces php artisan migrate:status

# Check app health
docker exec formynieces php artisan about

# Restart container
docker restart formynieces

# Shell into container
docker exec -it formynieces sh
```

---

## 10. What is NOT yet done

- [ ] **Resolve SQLite job_batches migration blocker** — see Section 6
- [ ] **Verify app loads at http://172.233.163.6:8080** — blocked by above
- [ ] **Apply pending system updates** — 69 updates including 5 security updates pending on the VPS. Run `sudo apt update && sudo apt upgrade -y` after the deploy is working.
- [ ] **Set up GitHub Actions** — for automatic deployment on `git push`. Discussed but not implemented. One file needed: `.github/workflows/deploy.yml`
- [ ] **Get a domain name** — currently accessible by IP only. When domain is obtained, add Caddy or Certbot for HTTPS.
- [ ] **Database backup strategy** — SQLite currently lives inside the container and is wiped on redeploy. Need to either mount it as a volume with proper initialisation, or add a backup cron job before real students use the platform.
- [ ] **Seed test users on production** — `php artisan db:seed --force` runs DatabaseSeeder which seeds syllabus modules and test users. Verify test accounts work after first successful deploy.

---

## 11. Test accounts (seeded by DatabaseSeeder)

| Role | Email | Password |
|---|---|---|
| Student | student@test.com | password |
| Parent/Guardian | parent@test.com | password |
| Admin (Filament) | Set via `php artisan make:filament-user` | — |

---

*For Laravel/backend technical details see `__Handoff/08JUN26_FORMY_NIECES_HANDOFF.md`*  
*For dashboard UX design details see `__Handoff/09JUN26_FORMYNIECES_DASHBOARD_HANDOFF.md`*
