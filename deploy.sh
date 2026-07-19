#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"
BUILD_COMPOSE_FILE="${BUILD_COMPOSE_FILE:-docker-compose.yml}"
APP_SERVICE="${APP_SERVICE:-app}"

step() {
  printf '\n[%s] %s\n' "$1" "$2"
}

run_cmd() {
  printf '  -> %s\n' "$*"
  "$@"
}

require_cmd() {
  if ! command -v "$1" >/dev/null 2>&1; then
    printf 'ERROR: required command not found: %s\n' "$1" >&2
    exit 1
  fi
}

step "INIT" "Starting deployment from ${ROOT_DIR}"
cd "${ROOT_DIR}"

require_cmd git
require_cmd docker

step "1/7" "Pull latest code"
run_cmd git pull --ff-only

step "2/7" "Install PHP dependencies (safe to rerun)"
run_cmd docker compose -f "${BUILD_COMPOSE_FILE}" run --rm composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

step "3/7" "Install frontend dependencies and build assets"
run_cmd docker compose -f "${BUILD_COMPOSE_FILE}" run --rm node ci
run_cmd docker compose -f "${BUILD_COMPOSE_FILE}" run --rm node run build

if ! [ -f "${COMPOSE_FILE}" ]; then
  printf 'ERROR: compose file not found: %s\n' "${COMPOSE_FILE}" >&2
  exit 1
fi

APP_CONTAINER_ID="$(docker compose -f "${COMPOSE_FILE}" ps -q "${APP_SERVICE}")"
if [ -z "${APP_CONTAINER_ID}" ]; then
  printf 'ERROR: app service container is not running. Start it first with docker compose -f %s up -d %s\n' "${COMPOSE_FILE}" "${APP_SERVICE}" >&2
  exit 1
fi

step "4/7" "Run database migrations"
run_cmd docker compose -f "${COMPOSE_FILE}" exec -T "${APP_SERVICE}" php artisan migrate --force

step "5/7" "Rebuild config cache"
run_cmd docker compose -f "${COMPOSE_FILE}" exec -T "${APP_SERVICE}" php artisan config:cache

step "6/7" "Rebuild view cache"
run_cmd docker compose -f "${COMPOSE_FILE}" exec -T "${APP_SERVICE}" php artisan view:cache

step "7/7" "Reload PHP-FPM in running app container"
run_cmd docker compose -f "${COMPOSE_FILE}" exec -T "${APP_SERVICE}" sh -lc 'kill -USR2 1'

step "DONE" "Deployment completed successfully"
