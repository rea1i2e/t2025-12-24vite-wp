# CLAUDE.md

## セッション引き継ぎ（Cursor ↔ Claude Code）

- 意味のある区切りごとに `memo/session-log.md` の当日見出し（`## YYYY-MM-DD`）へ追記する
- `- 次:` 行は常に最新1つに保つ（古い `- 次:` は `- 完了:` に書き換える）
- 触ったファイルがあれば `- ファイル: path` も添える
- **Claude Code**: セッション終了前・リミットが近いと感じたら `/handoff` を実行する
- **Cursor から Claude Code へ渡すとき**: 更新後 `ai-docs/claude-code-resume-prompt.md` をコピペして再開
- 詳細: `ai-docs/session-handoff.md`
- 正本スキル: ナレッジベース `skills/cross-tool-session-handoff/SKILL.md`
