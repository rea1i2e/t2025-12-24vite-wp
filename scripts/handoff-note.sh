#!/usr/bin/env bash
# 人間用: session-log に1行追記（AI が使えないときのフォールバック）
# env: HANDOFF_SESSION_LOG — 未設定時は PROJECT_ROOT/memo/session-log.md
set -euo pipefail

usage() {
  echo "usage: handoff-note.sh \"次: ...\"" >&2
  exit 1
}

[[ $# -ge 1 ]] || usage

root="$(cd "$(dirname "$0")/.." && pwd)"
log="${HANDOFF_SESSION_LOG:-$root/memo/session-log.md}"
date="$(date +%Y-%m-%d)"
note="$*"

mkdir -p "$(dirname "$log")"

if [[ ! -f "$log" ]]; then
  cat >"$log" <<'EOF'
# セッションログ

Claude Code / Cursor 作業の短い記録。整理できた内容は `plan/` `research/` `reports/` へ移す。

---

EOF
fi

if ! grep -q "## $date" "$log"; then
  printf '\n## %s\n' "$date" >>"$log"
fi

printf -- '- %s\n' "$note" >>"$log"
echo "appended to $log"
