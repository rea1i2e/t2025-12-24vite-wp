#!/usr/bin/env bash
# PreToolUse フック — 秘密情報ファイルを Bash 経由で読み出すコマンドをブロックする。
#
# 背景: settings.json の deny は Read ツールには効くが、Bash から `cat` / `grep` /
# リダイレクトで読む経路は塞げない。ここを塞ぐのが本フックの役割。
#
# 契約: stdin に PreToolUse の JSON。exit 2 でツール実行をブロックし、
# stderr の内容が Claude にフィードバックされる。exit 0 で許可。
#
# 正本: 2026-04-23kn/outputs/templates/hooks/secret-path-guard.sh
set -uo pipefail

INPUT="$(cat)"

# jq があれば正確に、なければ grep でフォールバック（フックは落とさない）
if command -v jq >/dev/null 2>&1; then
  TOOL="$(printf '%s' "$INPUT" | jq -r '.tool_name // empty')"
  CMD="$(printf '%s' "$INPUT" | jq -r '.tool_input.command // empty')"
else
  TOOL="$(printf '%s' "$INPUT" | sed -n 's/.*"tool_name"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p')"
  CMD="$INPUT"
fi

[[ "$TOOL" != "Bash" ]] && exit 0
[[ -z "$CMD" ]] && exit 0

# 雛形（*.example / *.sample）は実値を持たないので対象外
if printf '%s' "$CMD" | grep -qE '\.(example|sample)([[:space:]]|$)'; then
  exit 0
fi

# 秘密情報が入っているパス（読み出し対象にしてはいけないもの）
SECRET_PATHS='\.config/shin-account|\.config/notion|\.env\.deploy|/\.env([^A-Za-z0-9]|$)|\.htpasswd|id_rsa|\.ssh/|\.aws/credentials|mcp\.env'

# 中身を吐き出す系のコマンド。ファイル名を引数に取るものだけを対象にする
READERS='cat|less|more|head|tail|bat|xxd|od|strings|source|\.|env|printenv|open|cp|scp|rsync|curl|nc'

if printf '%s' "$CMD" | grep -qE "(^|[[:space:];&|(])($READERS)[[:space:]][^|;&]*($SECRET_PATHS)" \
   || printf '%s' "$CMD" | grep -qE "<[[:space:]]*[^|;&]*($SECRET_PATHS)"; then
  cat >&2 <<'MSG'
[secret-path-guard] 秘密情報ファイルの読み出しをブロックしました。

このファイル群（~/.config/shin-account/env, ~/.config/notion/mcp.env, .env.deploy,
.htpasswd, SSH 鍵など）は値そのものを画面・ログ・レポートに出さない運用です。

代替案:
  - 存在確認だけなら: ls -la <path>
  - 変数名だけ見たいなら: grep -oE '^(export )?[A-Z_]+=' <path>
  - 値が必要な処理は、値を表示せずスクリプト内で完結させる
  - 本当に値を見る必要があるなら、人間が自分のターミナルで実行する

参照: wiki/secret-rotation-runbook.md
MSG
  exit 2
fi

exit 0
