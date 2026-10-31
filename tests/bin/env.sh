# Sourced by the other scripts. TEST_SLOT=<n> gives an isolated environment (own database, own
# server port) so several people or agents can run tests at the same time:
#   TEST_SLOT=3  ->  database alies_test_3, server on port 8103
# Without a slot the defaults are used (alies_test, port 8089).
if [ -n "${TEST_SLOT:-}" ]; then
    export DB_NAME="${DB_NAME:-alies_test_${TEST_SLOT}}"
    export APP_PORT="${APP_PORT:-$((8100 + TEST_SLOT))}"
    export APP_URL="${APP_URL:-http://127.0.0.1:${APP_PORT}/}"
fi
