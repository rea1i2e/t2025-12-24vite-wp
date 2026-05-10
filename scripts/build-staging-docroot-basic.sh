#!/usr/bin/env bash
# staging/docroot/ に HTTP Basic 用 .htpasswd と、Basic + WordPress 既定リライトの .htaccess を生成する。
# GitHub Actions（deploy.yml）および手元検証用。平文パスワードは Git に載せない（生成物は .gitignore）。
#
# 必須環境変数:
#   CASE_ID — GITHUB_REPO_SLUG（例: 2026-05-10ex）
#
# 任意:
#   PARENT_DOMAIN  既定: rea1i2e.net
#   BASIC_STAGING_AUTH_NAME  既定: Staging
#   STAGING_AUTH_USER_FILE — Apache AuthUserFile（サーバー上の絶対パス）。省略時は
#     /${PARENT_DOMAIN}/public_html/${CASE_ID}.${PARENT_DOMAIN}/.htpasswd
#   STAGING_BASIC_USER / STAGING_BASIC_PASS — CASE_ID からの派生の代わりに明示（非推奨・履歴に残り得る）
#
# 用法:
#   CASE_ID=2026-05-10ex ./scripts/build-staging-docroot-basic.sh

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
CASE_ID="${CASE_ID:?CASE_ID（GITHUB_REPO_SLUG）を環境変数で指定してください}"
PARENT_DOMAIN="${PARENT_DOMAIN:-rea1i2e.net}"
AUTH_NAME="${BASIC_STAGING_AUTH_NAME:-Staging}"

derive_from_case_id() {
  local id="$1"
  if [[ "${id}" =~ ^([0-9]{4})-([0-9]{2})-([0-9]{2})([a-zA-Z]{2})$ ]]; then
    local mm="${BASH_REMATCH[2]}"
    local dd="${BASH_REMATCH[3]}"
    local sfx="${BASH_REMATCH[4]}"
    BASIC_USER="${sfx}${mm}${dd}"
    BASIC_PASS="${sfx}${mm}"
    return 0
  fi
  return 1
}

BASIC_USER=""
BASIC_PASS=""

if [[ -n "${STAGING_BASIC_USER:-}" && -n "${STAGING_BASIC_PASS:-}" ]]; then
  BASIC_USER="${STAGING_BASIC_USER}"
  BASIC_PASS="${STAGING_BASIC_PASS}"
elif derive_from_case_id "${CASE_ID}"; then
  :
else
  echo "CASE_ID が YYYY-MM-DDxx（末尾2文字は英字）に一致しません: ${CASE_ID}" >&2
  echo "一致しない運用では STAGING_BASIC_USER / STAGING_BASIC_PASS を環境変数で渡してください。" >&2
  exit 1
fi

if [[ -n "${STAGING_AUTH_USER_FILE:-}" ]]; then
  AUTH_USER_FILE_RESOLVED="${STAGING_AUTH_USER_FILE}"
else
  fqdn="${CASE_ID}.${PARENT_DOMAIN}"
  AUTH_USER_FILE_RESOLVED="/${PARENT_DOMAIN}/public_html/${fqdn}/.htpasswd"
fi

if ! command -v htpasswd >/dev/null 2>&1; then
  echo "htpasswd が見つかりません（Ubuntu: sudo apt-get install -y apache2-utils / macOS: brew install httpd）。" >&2
  exit 1
fi

OUT_DIR="${ROOT}/staging/docroot"
mkdir -p "${OUT_DIR}"
HTPASSWD_LINE="$(htpasswd -nbB "${BASIC_USER}" "${BASIC_PASS}" | tr -d '\r')"
printf '%s\n' "${HTPASSWD_LINE}" > "${OUT_DIR}/.htpasswd"
chmod 600 "${OUT_DIR}/.htpasswd" 2>/dev/null || true

ACCESS_TOP="$(printf 'AuthType Basic\nAuthName "%s"\nAuthUserFile %s\nRequire valid-user\n' "${AUTH_NAME}" "${AUTH_USER_FILE_RESOLVED}")"

{
  printf '%s\n\n' "${ACCESS_TOP}"
  printf '%s\n' '# 以下は WordPress 既定のリライト。サブディレクトリ設置のときは RewriteBase / と最終 RewriteRule を手直しすること。'
  cat <<'HTEOF'
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
HTEOF
} > "${OUT_DIR}/.htaccess"
chmod 644 "${OUT_DIR}/.htaccess" 2>/dev/null || true

echo "生成: ${OUT_DIR}/.htaccess" >&2
echo "生成: ${OUT_DIR}/.htpasswd" >&2
