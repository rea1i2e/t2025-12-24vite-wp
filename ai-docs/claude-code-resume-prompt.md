# Claude Code 再開プロンプト（Cursor からの引き継ぎ）

Cursor で作業を区切り、Claude Code で続けるときにコピペする。

## 起動

```bash
cd ~/Local\ Sites/t2025-12-24vite-wp/app/public/wp-content/themes/t2025-12-24vite-wp && claude
```

---

## プロンプト（コピペ用）

```
Cursor から引き継ぎ。前提を読んでから返答して。

## 必読

1. ai-docs/session-log.md（末尾の - 次: を最優先）
2. git status / git diff（未コミットの途中成果）
3. CLAUDE.md



## 今回の依頼

session-log の「次:」を実行して。
（必要ならここに補足）
```

---

## 区切り前（Cursor 側）

```
引き継ぎ: session-log を更新して。次は Claude Code で続ける。
```

---

## 関連

- [`session-handoff.md`](session-handoff.md)
- [`claude-code-kickoff-prompt.md`](claude-code-kickoff-prompt.md)（あれば）
