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
  -v /opt/formynieces-data/database:/var/www/html/database \
  -v /opt/formynieces-data/storage:/var/www/html/storage \
  formynieces:latest

echo "Running migrations..."
docker exec formynieces php artisan migrate --force
docker exec formynieces php artisan db:seed --force
docker exec formynieces php artisan config:cache
docker exec formynieces php artisan route:cache
docker exec formynieces php artisan view:cache

echo "Done. App running at http://172.233.163.6:8080"