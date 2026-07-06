#!/usr/bin/env bash
# StopFailure hook: API エラー（リミット等）時に session-log へ最低1行を追記する。
set -euo pipefail

root="${CLAUDE_PROJECT_DIR:-$(pwd)}"
input=$(cat)
export HANDOFF_HOOK_INPUT="$input"

detail=$(python3 <<'PY'
import json
import os
from pathlib import Path

try:
    data = json.loads(os.environ.get("HANDOFF_HOOK_INPUT", "{}"))
except json.JSONDecodeError:
    data = {}

error = (
    data.get("error_message")
    or data.get("error")
    or data.get("error_type")
    or "api error"
)
error = str(error).replace("\n", " ")[:120]

transcript = data.get("transcript_path", "")
last_user = ""
if transcript:
    transcript_path = Path(transcript)
    if transcript_path.exists():
        try:
            for line in reversed(transcript_path.read_text(encoding="utf-8").splitlines()):
                if not line.strip():
                    continue
                rec = json.loads(line)
                role = rec.get("role") or rec.get("type")
                if role not in ("user", "human"):
                    continue
                msg = rec.get("message", rec.get("content", ""))
                if isinstance(msg, list):
                    parts = []
                    for part in msg:
                        if isinstance(part, dict):
                            parts.append(str(part.get("text", "")))
                        else:
                            parts.append(str(part))
                    msg = " ".join(parts)
                last_user = str(msg).replace("\n", " ")[:100]
                break
        except (json.JSONDecodeError, OSError):
            pass

parts = [error]
if last_user:
    parts.append(f"last: {last_user}")
print(" | ".join(parts))
PY
)

"$root/scripts/handoff-snapshot.sh" "StopFailure" "$detail" "$root"
