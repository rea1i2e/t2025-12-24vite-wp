# CLAUDE.md

## セッション引き継ぎ（Cursor ↔ Claude Code）

- 意味のある区切りごとに `ai-docs/session-log.md` の当日見出し（`## YYYY-MM-DD`）へ追記する
- `- 次:` 行は常に最新1つに保つ（古い `- 次:` は `- 完了:` に書き換える）
- 触ったファイルがあれば `- ファイル: path` も添える
- **Claude Code**: セッション終了前・リミットが近いと感じたら `/handoff` を実行する
- **Cursor から Claude Code へ渡すとき**: 更新後 `ai-docs/claude-code-resume-prompt.md` をコピペして再開
- 詳細: `ai-docs/session-handoff.md`
- 正本スキル: ナレッジベース `skills/cross-tool-session-handoff/SKILL.md`

## Figma デザイン（mapping ストック）

- 通常実装の正本スキル: ナレッジベース `skills/figma-design-implementation/SKILL.md`

- 案件で Figma URL を受け取ったら **1回だけ** [`ai-docs/figma-design-kickoff-prompt.md`](ai-docs/figma-design-kickoff-prompt.md) で `ai-docs/figma-design-mapping.md` を整備する
- 以降のコーディングは [`ai-docs/figma-design-section-prompt.md`](ai-docs/figma-design-section-prompt.md) に従い **1セクションずつ** Figma MCP → 実装（ページ一括は禁止）
- **セクション実装後（省略禁止）**: 「セルフQA不要」の明示がなければ [`ai-docs/pre-human-qa-loop-prompt.md`](ai-docs/pre-human-qa-loop-prompt.md) で当該セクションのセルフQA（最大2周）→ `fb/qa` 残件 → 人間チェック待ち。正本: ナレッジ `wiki/ai-pre-human-qa-and-fb-rounds.md`
- 修正 FB は [`ai-docs/fb/`](ai-docs/fb/README.md)（`qa`＝制作側カンプ差分 / `client`＝先方回次で再現・変更混在可）。`qa` と client の `再現` は原因解明→修正→ブラウザ確認（`figma-qa-compare` は `fb/qa/` へ転記可）
- 正本 Figma 差し替え: ナレッジ `skills/figma-design-refresh-pixexport/SKILL.md`

## PHP 出力エスケープ規約

正本は `.cursor/rules/php-escape-output.mdc`（以下に取り込み。冒頭の frontmatter は Cursor 用なので無視してよい）:

@.cursor/rules/php-escape-output.mdc
