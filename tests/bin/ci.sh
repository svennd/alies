#!/usr/bin/env bash
# Runs the test suites the way a CI job would: fresh database, application server, all layers.
#
#   tests/bin/ci.sh                  pure + model + http
#   tests/bin/ci.sh pure             only one layer (pure | model | http)
#   tests/bin/ci.sh http --filter Smoke   extra arguments go to phpunit
#
# Against another build of the application (for example the CodeIgniter 4 port) that already runs
# somewhere: APP_URL=http://host:port/ SKIP_SERVE=1 tests/bin/ci.sh http
# (the application must use the test database, see tests/README.md).
#
# Exit code is 1 when any suite failed.
set -uo pipefail
cd "$(dirname "$0")/../.."
BIN=tests/bin

LAYER="${1:-all}"
[ $# -gt 0 ] && shift
EXTRA=("$@")

status=0
serving=0

stop_server() {
    if [ "$serving" = 1 ]; then "$BIN/serve.sh" stop >/dev/null || true; fi
}
trap stop_server EXIT

run() {
    echo
    echo "=== $1"
    shift
    "$BIN/dc.sh" "$@" || status=1
}

case "$LAYER" in
    all|pure|model|http) ;;
    *) echo "usage: $0 [all|pure|model|http] [phpunit arguments]" >&2; exit 2 ;;
esac

if [ "$LAYER" = all ] || [ "$LAYER" = pure ]; then
    run "pure tests" vendor/bin/phpunit -c phpunit.pure.xml.dist "${EXTRA[@]}"
fi

if [ "$LAYER" = all ] || [ "$LAYER" = model ] || [ "$LAYER" = http ]; then
    echo
    echo "=== database"
    "$BIN/reset-db.sh" || exit 1
fi

if [ "$LAYER" = all ] || [ "$LAYER" = model ]; then
    run "model tests (CodeIgniter in-process)" vendor/bin/phpunit -c phpunit.xml.dist --testsuite model "${EXTRA[@]}"
fi

if [ "$LAYER" = all ] || [ "$LAYER" = http ]; then
    if [ -z "${SKIP_SERVE:-}" ]; then
        "$BIN/serve.sh" start || exit 1
        serving=1
    fi
    run "http tests" vendor/bin/phpunit -c phpunit.http.xml.dist "${EXTRA[@]}"
fi

echo
if [ "$status" = 0 ]; then echo "ALL SUITES PASSED"; else echo "SOME SUITES FAILED"; fi
exit "$status"
