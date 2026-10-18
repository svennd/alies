#!/usr/bin/env bash
# Starts or stops the application server used by the HTTP tests (PHP built-in server, CI_ENV=testing).
#
#   tests/bin/serve.sh start | stop | restart | status | log
#
# It runs inside the php container (same network as the database) and listens on 127.0.0.1:8089
# there, which is where the tests expect it (APP_URL, default http://127.0.0.1:8089/).
set -euo pipefail
cd "$(dirname "$0")/../.."
DC="$(pwd)/tests/bin/dc.sh"
# shellcheck source=env.sh
. "$(pwd)/tests/bin/env.sh"

PORT="${APP_PORT:-8089}"
PIDFILE=/tmp/alies-test-server-$PORT.pid
LOGFILE=/tmp/alies-test-server-$PORT.log

running() {
    "$DC" sh -c "test -f $PIDFILE && kill -0 \$(cat $PIDFILE) 2>/dev/null" >/dev/null 2>&1
}

ready() {
    "$DC" php -r '$c=@fsockopen("127.0.0.1", (int) $argv[1], $e, $s, 1); exit($c ? 0 : 1);' "$PORT" >/dev/null 2>&1
}

case "${1:-status}" in
    start)
        if running; then echo "already running"; exit 0; fi
        if "$DC" test -f DEBUG_MODE; then
            echo "a DEBUG_MODE file exists in the repository root, remove it first (it changes the application's behaviour)" >&2
            exit 1
        fi
        # detached: the server must outlive this exec session
        CMD="echo \$\$ > $PIDFILE; exec env PHP_CLI_SERVER_WORKERS=2 php -d short_open_tag=0 -S 127.0.0.1:$PORT -t . tests/bin/router.php > $LOGFILE 2>&1"
        if [ -f /.containerenv ] || [ -f /.dockerenv ] || ! command -v podman >/dev/null 2>&1; then
            (setsid sh -c "$CMD" >/dev/null 2>&1 &)
        else
            podman exec -d -w /var/www/html -e CI_ENV=testing ${APP_URL:+-e APP_URL="$APP_URL"} ${DB_NAME:+-e DB_NAME="$DB_NAME"} ${DB_HOST:+-e DB_HOST="$DB_HOST"} "${APP_CONTAINER:-alies-php}" sh -c "$CMD" >/dev/null
        fi
        for _ in $(seq 1 50); do
            if ready; then echo "listening on 127.0.0.1:$PORT (inside the container), database ${DB_NAME:-alies_test}"; exit 0; fi
            sleep 0.2
        done
        echo "server did not start, see: tests/bin/serve.sh log" >&2
        exit 1
        ;;
    stop)
        "$DC" sh -c "test -f $PIDFILE && kill \$(cat $PIDFILE) 2>/dev/null; rm -f $PIDFILE" || true
        echo "stopped"
        ;;
    restart)
        "$0" stop
        "$0" start
        ;;
    status)
        if running; then echo "running"; else echo "not running"; exit 1; fi
        ;;
    log)
        "$DC" sh -c "cat $LOGFILE"
        ;;
    *)
        echo "usage: $0 start|stop|restart|status|log" >&2
        exit 2
        ;;
esac
