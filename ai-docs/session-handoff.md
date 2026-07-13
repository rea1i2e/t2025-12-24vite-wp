# セッション引き継ぎ — Cursor ↔ Claude Code

正本スキル: ナレッジベース `skills/cross-tool-session-handoff/SKILL.md`

`ai-docs/session-log.md`（または `--session-log` で指定したパス）に状態を残し、ツールをまたいで続ける。

## 3層構成（両方向共通）

| 層 | Claude Code | Cursor |
|----|-------------|--------|
| 1 | `CLAUDE.md` 運用ルール | `.cursor/rules/session-handoff.mdc` |
| 2 | `/handoff` | 「引き継ぎ」指示 / `scripts/handoff-note.sh` |
| 3 | `StopFailure` フック | `sessionEnd` フック |

## Claude Code → Cursor

1. `@ai-docs/session-log.md` と作業中ファイルを添付
2. 「session-log と git diff を読んで続きから」

## Cursor → Claude Code

1. `claude` 起動（下記 cwd）
2. [`claude-code-resume-prompt.md`](claude-code-resume-prompt.md) をコピペ

```bash
cd ~/Local\ Sites/t2025-12-24vite-wp/app/public/wp-content/themes/t2025-12-24vite-wp && claude
```

## 人間用

```bash
./scripts/handoff-note.sh "次: …"
```

## 再インストール

```bash
python3 /path/to/2026-04-23kn/skills/cross-tool-session-handoff/scripts/install-handoff.py --target .
```
