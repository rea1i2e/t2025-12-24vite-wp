#!/usr/bin/env bash
set -euo pipefail

# Setup GitHub Actions secrets via GitHub CLI (gh)
# - Reads values from .env.deploy (not committed)
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

# shellcheck disable=SC1090
set -a
source "${ENV_FILE}"
set +a

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

echo "Setting GitHub Actions secrets for this repo..."

gh secret set FTP_SERVER     --body "${FTP_SERVER}"
gh secret set FTP_USERNAME   --body "${FTP_USERNAME}"
gh secret set FTP_PASSWORD   --body "${FTP_PASSWORD}"
gh secret set FTP_SERVER_DIR --body "${FTP_SERVER_DIR}"

echo "Done."
echo "You can verify names (values are hidden) with: gh secret list"


