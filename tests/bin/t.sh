#!/usr/bin/env bash
# Runs one layer of tests and prints a compact result (failures with their first lines only).
#
#   TEST_SLOT=3 tests/bin/t.sh http  tests/Http/Events            # a directory or file
#   TEST_SLOT=3 tests/bin/t.sh http  tests/Http/Events --filter Lock
#   TEST_SLOT=3 tests/bin/t.sh model tests/Models/FooTest.php
#   tests/bin/t.sh pure
#
# Creates the database of the slot and starts that slot's server when needed. Set LINES_MAX to see
# more output (default 80). Set RESET=1 to rebuild the database first.
set -uo pipefail
cd "$(dirname "$0")/../.."
BIN=tests/bin
. "$BIN/env.sh"

layer="${1:-}"; shift || true
case "$layer" in
    pure)  config=phpunit.pure.xml.dist; extra=() ;;
    http)  config=phpunit.http.xml.dist; extra=() ;;
    model) config=phpunit.xml.dist; extra=(--testsuite model) ;;
    *) echo "usage: [TEST_SLOT=n] $0 pure|http|model [phpunit arguments or paths]" >&2; exit 2 ;;
esac

if [ "$layer" != pure ]; then
    if [ -n "${RESET:-}" ] || ! "$BIN/dc.sh" php tests/bin/reset-db.php --check >/dev/null 2>&1; then
        "$BIN/reset-db.sh" >&2 || exit 1
    fi
fi
if [ "$layer" = http ]; then
    "$BIN/serve.sh" status >/dev/null 2>&1 || "$BIN/serve.sh" start >&2 || exit 1
fi

# a path argument replaces the testsuite option
args=("$@")
for a in "${args[@]:-}"; do case "$a" in tests/*) extra=() ;; esac; done

"$BIN/dc.sh" vendor/bin/phpunit -c "$config" "${extra[@]}" "${args[@]}" --no-progress --do-not-record-test-run-history 2>&1 \
    | sed 's/\x1b\[[0-9;]*m//g' | grep -v '^\s*$' \
    | grep -vE '^/var/www/html/vendor|^Caused by|^PHPUnit |^Runtime:|^Configuration:' | cut -c1-260 \
    | awk '/^[0-9]+\) /{c=0} {c++; if (c<=8) print}' | head -"${LINES_MAX:-80}"
