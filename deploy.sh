#!/bin/bash
set -e

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
  -p 8080:8080 \
  -v /opt/formynieces-data/db:/var/www/html/db \
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
