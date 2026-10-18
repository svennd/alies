#!/usr/bin/env bash
# Runs a command inside the application container, from the repository root.
#
#   tests/bin/dc.sh php tests/bin/reset-db.php
#   tests/bin/dc.sh vendor/bin/phpunit
#
# Already inside the container (or on a machine where `db` resolves)? Then the command is run as is.
# Override the container name with APP_CONTAINER. TEST_SLOT=<n> selects an isolated database/port.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
# shellcheck source=env.sh
. "$(dirname "$0")/env.sh"

if [ -f /.containerenv ] || [ -f /.dockerenv ] || ! command -v podman >/dev/null 2>&1; then
    cd "$ROOT"
    exec env CI_ENV=testing "$@"
fi

CONTAINER="${APP_CONTAINER:-alies-php}"
exec podman exec -w /var/www/html \
    -e CI_ENV=testing \
    ${APP_URL:+-e APP_URL="$APP_URL"} \
    ${APP_PORT:+-e APP_PORT="$APP_PORT"} \
    ${DB_HOST:+-e DB_HOST="$DB_HOST"} \
    ${DB_NAME:+-e DB_NAME="$DB_NAME"} \
    ${TEST_SLOT:+-e TEST_SLOT="$TEST_SLOT"} \
    "$CONTAINER" "$@"
