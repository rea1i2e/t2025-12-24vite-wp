# デザイントークン（案件キックオフ用・雛形）

セクション実装の**前**に埋める正本。テンプレ同梱の仮フォント（例: Noto Sans JP）のままコーディングしない。

- 作業手順: [`PROMPT.md`](./PROMPT.md) ステップ 0
- フォント設置: Skill `font-setup-web`、方針 `wiki/web-fonts-guidelines.md`、コマンド `raw/fonts/README-font-compress.md`
- Figma 案件でも、mapping 整備とあわせて本ファイルを埋めてよい

## フォント

| 用途 | ファミリー（カンプ表記） | CSS 変数 | ウェイト（カンプ → CSS） | woff2 ファイル名 |
|------|--------------------------|----------|--------------------------|------------------|
| 本文・UI | （例: Zen Kaku Gothic New） | `--base-font-family` | （例: Medium→500, Bold→700） | |
| 欧文・数字・電話（任意） | （例: Fira Sans） | `--font-family-en` | | |

- **バリアブルがあれば優先**（無ければ静的ウェイト。使うウェイトだけ載せる）
- **preload:** FV 用 **1 ウェイト 1 ファイルのみ**（`header.php` + `ty_vite_asset_url('src/assets/fonts/…')`）
- **反映先:** `src/assets/sass/base/_root.scss`（`@font-face` + `:root`）、`body` は `var(--base-font-family)`
- **出典:** XD なら `export_*.json` の `fontFamily` / `fontStyle` を全件集計。Figma なら MCP / Dev Mode

## 色・その他（任意）

| トークン | 値 | 備考 |
|----------|-----|------|
| `--color-theme` | | |
| `--color-accent` | | |

## 更新履歴

| 日付 | 内容 |
|------|------|
| YYYY-MM-DD | 雛形作成（案件で上書き） |
