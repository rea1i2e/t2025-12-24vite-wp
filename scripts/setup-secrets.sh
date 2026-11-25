#!/usr/bin/env bash
set -euo pipefail

# Setup GitHub Actions secrets via GitHub CLI (gh)
# - Reads values from .env.deploy (not committed)
#
# テスト: リポジトリ Secrets（FTP_*）
# 本番: Environment production の Secrets（PROD_FTP_*）
#
# Usage:
#   ./scripts/setup-secrets.sh
#
# Requirements:
#   - gh installed
#   - gh auth login done

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="${ROOT_DIR}/.env.deploy"

if ! command -v gh >/dev/null 2>&1; then
  echo "Error: gh (GitHub CLI) is not installed." >&2
  echo "Install it: https://cli.github.com/" >&2
  exit 1
fi

if ! gh auth status >/dev/null 2>&1; then
  echo "Error: gh is not authenticated." >&2
  echo "Run: gh auth login" >&2
  exit 1
fi

if [[ ! -f "${ENV_FILE}" ]]; then
  echo "Error: ${ENV_FILE} not found." >&2
  echo "Create it from env.deploy.example:" >&2
  echo "  cp env.deploy.example .env.deploy" >&2
  exit 1
fi

# source を使わず行解析で読む（パスワード等の特殊文字対応）
#
# 未定義キーでも空文字を返して続行する。`set -euo pipefail` 下では
# grep の終了コード 1 が pipefail で伝播し、コマンド置換の失敗として
# スクリプト全体が停止してしまうため、明示的に握りつぶす。
get_var() {
  local key="$1"
  grep -m1 "^${key}=" "${ENV_FILE}" | cut -d'=' -f2- || true
}

FTP_SERVER="$(get_var FTP_SERVER)"
FTP_USERNAME="$(get_var FTP_USERNAME)"
FTP_PASSWORD="$(get_var FTP_PASSWORD)"
FTP_SERVER_DIR="$(get_var FTP_SERVER_DIR)"
DISCORD_WEBHOOK="$(get_var DISCORD_WEBHOOK)"
TEST_URL="$(get_var TEST_URL)"
GH_REPO="$(get_var GH_REPO)"
PROD_FTP_SERVER="$(get_var PROD_FTP_SERVER)"
PROD_FTP_USERNAME="$(get_var PROD_FTP_USERNAME)"
PROD_FTP_PASSWORD="$(get_var PROD_FTP_PASSWORD)"
PROD_FTP_SERVER_DIR="$(get_var PROD_FTP_SERVER_DIR)"
PROD_URL="$(get_var PROD_URL)"

if [[ -z "${GH_REPO}" ]]; then
  GH_REPO="$(gh repo view --json nameWithOwner --jq .nameWithOwner)"
fi

required_vars=(FTP_SERVER FTP_USERNAME FTP_PASSWORD FTP_SERVER_DIR)
missing=()
for v in "${required_vars[@]}"; do
  if [[ -z "${!v:-}" ]]; then
    missing+=("${v}")
  fi
done

if (( ${#missing[@]} > 0 )); then
  echo "Error: missing variables in .env.deploy: ${missing[*]}" >&2
  exit 1
fi

gh_secret() {
  local name="$1"
  local body="$2"
  printf '%s' "${body}" | gh secret set "${name}" --repo "${GH_REPO}"
}

gh_env_secret() {
  local name="$1"
  local body="$2"
  printf '%s' "${body}" | gh secret set "${name}" --repo "${GH_REPO}" --env production
}

ensure_production_environment() {
  local api="repos/${GH_REPO}/environments/production"
  if ! gh api "${api}" >/dev/null 2>&1; then
    gh api --method PUT "${api}" --input - << 'EOF'
{"deployment_branch_policy":{"protected_branches":false,"custom_branch_policies":true}}
EOF
  fi

  local names
  names="$(gh api "${api}/deployment-branch-policies" --jq '[.branch_policies[].name] | join(" ")')"
  if [[ " ${names} " != *" main "* ]]; then
    gh api --method POST "${api}/deployment-branch-policies" --input - << 'EOF'
{"name":"main","type":"branch"}
EOF
  fi
}

echo "Setting test repository secrets for ${GH_REPO}..."

gh_secret FTP_SERVER     "${FTP_SERVER}"
gh_secret FTP_USERNAME   "${FTP_USERNAME}"
gh_secret FTP_PASSWORD   "${FTP_PASSWORD}"
gh_secret FTP_SERVER_DIR "${FTP_SERVER_DIR}"

if [[ -n "${DISCORD_WEBHOOK}" ]]; then
  gh_secret DISCORD_WEBHOOK "${DISCORD_WEBHOOK}"
fi

if [[ -n "${TEST_URL}" ]]; then
  gh_secret TEST_URL "${TEST_URL}"
fi

prod_set=0
[[ -n "${PROD_FTP_SERVER}" ]] && prod_set=$((prod_set + 1))
[[ -n "${PROD_FTP_USERNAME}" ]] && prod_set=$((prod_set + 1))
[[ -n "${PROD_FTP_PASSWORD}" ]] && prod_set=$((prod_set + 1))
[[ -n "${PROD_FTP_SERVER_DIR}" ]] && prod_set=$((prod_set + 1))

if (( prod_set == 0 )); then
  echo "Production FTP secrets were left empty. Test secrets only."
  echo "Done."
  echo "Verify names with: gh secret list --repo ${GH_REPO}"
  exit 0
fi

if (( prod_set != 4 )); then
  echo "Error: PROD_FTP_SERVER, PROD_FTP_USERNAME, PROD_FTP_PASSWORD, and PROD_FTP_SERVER_DIR must all be set, or all be empty." >&2
  exit 1
fi

if [[ "${PROD_FTP_SERVER}" == "${FTP_SERVER}" && "${PROD_FTP_USERNAME}" == "${FTP_USERNAME}" ]]; then
  echo "Error: production FTP server and username match the test account. Use a different account." >&2
  exit 1
fi

echo "Setting Environment production secrets for ${GH_REPO}..."
echo "Required reviewers are not available on GitHub Free private repositories. Deployment branch is main only."

ensure_production_environment

gh_env_secret PROD_FTP_SERVER     "${PROD_FTP_SERVER}"
gh_env_secret PROD_FTP_USERNAME   "${PROD_FTP_USERNAME}"
gh_env_secret PROD_FTP_PASSWORD   "${PROD_FTP_PASSWORD}"
gh_env_secret PROD_FTP_SERVER_DIR "${PROD_FTP_SERVER_DIR}"

if [[ -n "${PROD_URL}" ]]; then
  gh_env_secret PROD_URL "${PROD_URL}"
fi

verify_vars=(FTP_SERVER FTP_USERNAME FTP_PASSWORD FTP_SERVER_DIR)
registered_names="$(gh secret list --repo "${GH_REPO}" --json name --jq '.[].name')"
verify_missing=()
for secret_name in "${verify_vars[@]}"; do
  if ! grep -Fxq "${secret_name}" <<< "${registered_names}"; then
    verify_missing+=("${secret_name}")
  fi
done

if (( ${#verify_missing[@]} > 0 )); then
  echo "Error: GitHub Secrets の登録確認に失敗しました: ${verify_missing[*]}" >&2
  exit 1
fi

prod_names="$(gh secret list --env production --repo "${GH_REPO}" --json name --jq '.[].name')"
for secret_name in PROD_FTP_SERVER PROD_FTP_USERNAME PROD_FTP_PASSWORD PROD_FTP_SERVER_DIR; do
  if ! grep -Fxq "${secret_name}" <<< "${prod_names}"; then
    echo "Error: Environment production Secrets の登録確認に失敗しました: ${secret_name}" >&2
    exit 1
  fi
done

echo "Done. GitHub Secrets の必須名を確認しました（値は取得していません）。"
echo "Verify production names with: gh secret list --env production --repo ${GH_REPO}"
