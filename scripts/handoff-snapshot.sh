#!/usr/bin/env bash
# session-log へスナップショット1行を追記（Claude Code / Cursor フック共通）
# usage: handoff-snapshot.sh TAG "detail" [PROJECT_ROOT]
# env: HANDOFF_SESSION_LOG — ログパス（未設定時 PROJECT_ROOT/memo/session-log.md）
set -euo pipefail

tag="${1:?usage: handoff-snapshot.sh TAG \"detail\" [PROJECT_ROOT]}"
detail="${2:-}"
root="${3:-${CLAUDE_PROJECT_DIR:-$(pwd)}}"
log="${HANDOFF_SESSION_LOG:-$root/memo/session-log.md}"

mkdir -p "$(dirname "$log")"

export HANDOFF_SNAPSHOT_TAG="$tag"
export HANDOFF_SNAPSHOT_DETAIL="$detail"
export HANDOFF_SNAPSHOT_ROOT="$root"
export HANDOFF_SNAPSHOT_LOG="$log"

python3 <<'PY'
import os
import subprocess
from datetime import datetime
from pathlib import Path

tag = os.environ["HANDOFF_SNAPSHOT_TAG"]
detail = os.environ.get("HANDOFF_SNAPSHOT_DETAIL", "").replace("\n", " ")[:120]
root = Path(os.environ["HANDOFF_SNAPSHOT_ROOT"])
log_path = Path(os.environ["HANDOFF_SNAPSHOT_LOG"])

try:
    git_lines = subprocess.check_output(
        ["git", "status", "--short"],
        cwd=root,
        stderr=subprocess.DEVNULL,
        text=True,
    ).strip().splitlines()[:5]
    git_summary = "; ".join(line for line in git_lines if line) or "clean"
except (subprocess.CalledProcessError, FileNotFoundError):
    git_summary = "n/a"

date = datetime.now().strftime("%Y-%m-%d")
ts = datetime.now().strftime("%H:%M")

if log_path.exists():
    content = log_path.read_text(encoding="utf-8")
else:
    content = (
        "# セッションログ\n\n"
        "Claude Code / Cursor 作業の短い記録。"
        "整理できた内容は `plan/` `research/` `reports/` へ移す。\n\n"
        "---\n"
    )

header = f"## {date}"
if header not in content:
    if not content.endswith("\n"):
        content += "\n"
    content += f"\n{header}\n"

line = f"- [{ts}] ⚠️ {tag}"
if detail:
    line += f": {detail}"
line += f" | git: {git_summary}\n"

log_path.write_text(content + line, encoding="utf-8")
PY
