#!/usr/bin/env bash

set -euo pipefail

APP_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$APP_ROOT"

ENV_FILE=".env"
RUN_MIGRATION=1
INSTALL_DEV=0
FORCE_KEY=0

usage() {
  cat <<'EOF'
Usage:
  ./setup-vps-cpanel.sh [options]

Options:
  --env=FILE        Environment file to use (default: .env)
  --no-migrate      Skip database migration
  --with-dev        Install composer dev dependencies
  --force-key       Force regenerate APP_KEY
  -h, --help        Show this help

Example:
  ./setup-vps-cpanel.sh --env=.env --no-migrate
EOF
}

log() { printf "\n[INFO] %s\n" "$1"; }
warn() { printf "\n[WARN] %s\n" "$1"; }
fail() { printf "\n[ERROR] %s\n" "$1"; exit 1; }

for arg in "$@"; do
  case "$arg" in
    --env=*)
      ENV_FILE="${arg#*=}"
      ;;
    --no-migrate)
      RUN_MIGRATION=0
      ;;
    --with-dev)
      INSTALL_DEV=1
      ;;
    --force-key)
      FORCE_KEY=1
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      fail "Unknown argument: $arg"
      ;;
  esac
done

command -v php >/dev/null 2>&1 || fail "PHP not found in PATH."
command -v composer >/dev/null 2>&1 || fail "Composer not found in PATH."

if [[ ! -f artisan ]]; then
  fail "artisan file not found. Run script from Laravel project root."
fi

if [[ ! -f "$ENV_FILE" ]]; then
  if [[ -f .env.example ]]; then
    log "$ENV_FILE not found. Copying from .env.example"
    cp .env.example "$ENV_FILE"
  else
    fail "$ENV_FILE and .env.example both not found."
  fi
fi

log "Installing composer dependencies"
if [[ "$INSTALL_DEV" -eq 1 ]]; then
  composer install --no-interaction --prefer-dist --optimize-autoloader
else
  composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
fi

if [[ "$FORCE_KEY" -eq 1 ]]; then
  log "Forcing APP_KEY regeneration"
  php artisan key:generate --force
else
  if grep -qE '^APP_KEY=\s*$' "$ENV_FILE"; then
    log "APP_KEY empty, generating key"
    php artisan key:generate --force
  else
    log "APP_KEY already exists, skip key generation"
  fi
fi

log "Ensuring runtime directories and permissions"
mkdir -p storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache || warn "Permission update skipped (not permitted by current user)."

log "Running storage:link"
php artisan storage:link || warn "storage:link skipped (already exists or symlink not allowed)."

if [[ "$RUN_MIGRATION" -eq 1 ]]; then
  log "Running database migrations"
  php artisan migrate --force
else
  warn "Migration skipped by --no-migrate"
fi

log "Refreshing Laravel caches"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache || warn "route:cache failed (likely route closure exists)."
php artisan view:cache
php artisan event:cache || warn "event:cache failed."

log "Setup completed."
echo "Next: verify APP_URL, DB, S3, MAIL values in $ENV_FILE and test login + key workflows."
