#!/usr/bin/env bash
# Cursor sessionEnd hook: チャット終了時に session-log へ最低1行を追記する。
set -euo pipefail

root="$(pwd)"
"$root/ai-docs/tools/handoff-snapshot.sh" "SessionEnd (Cursor)" "cursor chat ended" "$root"
