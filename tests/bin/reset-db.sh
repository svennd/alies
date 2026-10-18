#!/usr/bin/env bash
# Rebuilds the test database (alies_test). Runs inside the php container, see tests/bin/dc.sh.
#   tests/bin/reset-db.sh          full rebuild
#   tests/bin/reset-db.sh --seed   reseed only
set -euo pipefail
cd "$(dirname "$0")/../.."
exec "$(dirname "$0")/dc.sh" php tests/bin/reset-db.php "$@"
