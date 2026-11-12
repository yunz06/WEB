#!/usr/bin/env bash
# Local-only HTTP smoke test; no MySQL success is claimed by these checks.
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."
PORT="${NEWS_HUB_TEST_PORT:-18748}"
export DASHBOARD_STANDALONE_DEMO=true
export DEMO_ADMIN_USER=local-test-admin
export DEMO_ADMIN_PASSWORD=local-test-credential-not-for-production
TMP_DIR="$(mktemp -d)"
SERVER_PID=''
cleanup() {
  if [[ -n "$SERVER_PID" ]]; then
    kill "$SERVER_PID" 2>/dev/null || true
    wait "$SERVER_PID" 2>/dev/null || true
  fi
  rm -rf "$TMP_DIR"
}
trap cleanup EXIT
php -S "127.0.0.1:$PORT" -t public >"$TMP_DIR/php_server.log" 2>&1 &
SERVER_PID=$!
sleep 1
if ! kill -0 "$SERVER_PID" 2>/dev/null; then
  echo '[BLOCKED] Không thể chạy PHP web server trên cổng đã chọn.'
  cat "$TMP_DIR/php_server.log"
  exit 2
fi
BASE="http://127.0.0.1:$PORT"
PASSED=0
FAILED=0
check() {
  local label="$1" want="$2" actual="$3"
  if [[ "$actual" == "$want" ]]; then
    echo "[PASS] $label: $actual"
    PASSED=$((PASSED+1))
  else
    echo "[FAIL] $label: mong đợi=$want, thực tế=$actual"
    FAILED=$((FAILED+1))
  fi
}
code() { curl --max-time 5 -sS -o "$TMP_DIR/body" -D "$TMP_DIR/headers" -w '%{http_code}' "$@"; }
check 'H01 Không đăng nhập bị chặn' 401 "$(code "$BASE/dashboard.php")"
if grep -qi '^WWW-Authenticate: Basic ' "$TMP_DIR/headers"; then check 'H02 Có Basic Auth challenge' yes yes; else check 'H02 Có Basic Auth challenge' yes no; fi
check 'H03 Sai mật khẩu bị chặn' 401 "$(code -u 'local-test-admin:wrong' "$BASE/dashboard.php")"
check 'H04 Không lộ thông tin admin trên trang 401' no "$(grep -Fq "$DEMO_ADMIN_PASSWORD" "$TMP_DIR/body" && echo yes || echo no)"
check 'H05 CSS trả thành công' 200 "$(code "$BASE/css/dashboard.css")"
check 'H06 JS trả thành công' 200 "$(code "$BASE/js/dashboard.js")"
# For a machine lacking a MySQL driver, a correctly authenticated request must fail closed (503).
if ! php -r 'exit(in_array("mysql", PDO::getAvailableDrivers(), true) ? 0 : 1);'; then
  check 'H07 Không MySQL driver không hiện dữ liệu giả' 503 "$(code -u "$DEMO_ADMIN_USER:$DEMO_ADMIN_PASSWORD" "$BASE/dashboard.php")"
else
  echo '[BLOCKED] H07 Không thể xác định HTTP 200 cho MySQL nếu chưa xác nhận server/DSN; dùng demo_database_test.php.'
fi
echo "HTTP SMOKE: PASS=$PASSED FAIL=$FAILED"
if [[ "$FAILED" -gt 0 ]]; then exit 1; fi
