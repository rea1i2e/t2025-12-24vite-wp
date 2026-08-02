# XD デザインカンプ — 規約ポインタと XD 固有メモ

XD JSON / PNG から HTML+SCSS（WordPress テーマ）を書くときの案内。  
**コーディング規約の本文はここに複製しない。** 実装前に下表の正本を Read すること。

## 正本（必ずこちらを従う）

| 内容 | パス |
|------|------|
| 案件入口 | [`AGENTS.md`](../../AGENTS.md) |
| WP テンプレ固有（`ty_`・画像・Sass 前提など） | [`ai-docs/architecture.md`](../architecture.md) |
| 汎用規約の入口 | `/Users/yoshiaki/working/2026-04-23kn/wiki/coding-conventions.md` |
| Sass / CSS（**バリアントは `data-*`**） | `/Users/yoshiaki/working/2026-04-23kn/wiki/coding-sass.md` |
| HTML / EJS 方針（**画像 1MB・lazy 等**） | `/Users/yoshiaki/working/2026-04-23kn/wiki/coding-ejs-html.md` |
| 画像解像度 約 2x（Skill） | `/Users/yoshiaki/working/2026-04-23kn/skills/image-scale-check/SKILL.md` |
| 画像ファイルサイズ 1MB（Skill） | `/Users/yoshiaki/working/2026-04-23kn/skills/image-size-check/SKILL.md` |
| PHP | `/Users/yoshiaki/working/2026-04-23kn/wiki/coding-php.md` |
| a11y | `/Users/yoshiaki/working/2026-04-23kn/wiki/a11y-baseline.md` |
| PHP 出力エスケープ | [`.cursor/rules/php-escape-output.mdc`](../../.cursor/rules/php-escape-output.mdc) |
| Sass mixin 利用 | [`.cursor/rules/sass-use-mixins.mdc`](../../.cursor/rules/sass-use-mixins.mdc) |
| デザイントークン（フォント・レイアウト幅等） | [`DESIGN-TOKENS.md`](./DESIGN-TOKENS.md) |
| Web フォント設置（Skill） | `/Users/yoshiaki/working/2026-04-23kn/.cursor/skills/font-setup-web/SKILL.md` |
| Web フォント方針 | `/Users/yoshiaki/working/2026-04-23kn/wiki/web-fonts-guidelines.md` |

作業手順（アートボード順・検証）は [`PROMPT.md`](./PROMPT.md)。**ステップ 0（フォント・レイアウト幅）を飛ばさない。**

## フォント・デザイントークン（重要）

- 正本: [`DESIGN-TOKENS.md`](./DESIGN-TOKENS.md)。未整備ならセクション実装前に埋める（[`PROMPT.md`](./PROMPT.md) ステップ 0）。
- テンプレ仮フォント（Noto 等）のまま進めない。カンプの `fontFamily` を自己ホストで `_root.scss` に載せる。
- 本文: `var(--base-font-family)`。欧文・数字・電話: `var(--font-family-en)`（定義がある場合）。
- preload は FV 用 1 ファイルのみ。`font-display: swap` 必須。
- **`$width-pc` / `$inner-pc`**（`_setting.scss`）もステップ 0 で確定する。後から変えると `l-inner`・リキッド・`vw()` がずれる。

## Sass / マークアップの手がかり

- コンポーネント SCSS は先頭で `@use "../global" as *;`（既存 partial に合わせる）。
- 単位は `rem()` / 必要なら `maxrem()`。ブレイクポイント差分は `@include mq() { … }`（セレクタの `&` ネストはしない。詳細は `coding-sass.md`）。
- PHP/HTML に出した当該ブロックのクラスは、**マークアップ出現順**で SCSS に並べる。指定がなくても空セレクタ `{}` を残す（`coding-sass.md`「マークアップ出現順にセレクタを並べ、空でも書く」）。
- **バリアントは BEM modifier（`--xxx`）ではなく `data-*`**（例: `data-color="contact"`）。SCSS は `.p-block__el[data-color="contact"]`。正本は `coding-sass.md` の「バリアント設計」。
- CSS 変数・繰り返しトークンは `src/assets/sass` の既存（`global` / `base/_root.scss` 等）と [`DESIGN-TOKENS.md`](./DESIGN-TOKENS.md) を優先。新規変数は必要なときだけ追加。
- HTML 内のテーマ画像は `ty_img()` / `ty_theme_image_url()`（`get_template_directory_uri()/images/` は使わない）。
- ホバーは `coding-sass.md` および既存 mixin（`any-hover` 等）に従う。**フォーカスアウトラインを `outline: none` で消さない**（a11y 正本）。
- ボタン・見出し・inner などの見た目は、このファイルのサンプルではなく **既存クラス / 型録 / XD カンプ** を正とする。

## 画像パス（書き出し前提・重要）

- **`demo/dummy*.jpg` などの仮画像で埋めない。** 実装時点で **本番予定のパスを `ty_img('…')` に書く。**
- ファイルが未配置でもパスを先に固定し、人間が同じファイル名で XD から `src/assets/images/` へ書き出す。未設置時は `ty_img` が `.u-img-missing` でパスを画面表示する。
- **命名:** `{page-slug}/{ブロック名}_{名前}.{ext}`（ブロック名は `p-` / `c-` / `l-` を除く。共通は `common/`）。
  - **名前は意味のある語を優先**（`logo` / `illust` / `thumb` / `icon` など）。例: `top/top-mv_illust.png`、`common/header_logo.svg`
  - `_asset` など汎用すぎる語は使わない。どうしても付かないときだけ `_01` 連番でよい
  - テンプレ同梱のデモ画像は使わない（書き出しパスを先に書く）
- 正本一覧: [`IMAGE-EXPORT.md`](./IMAGE-EXPORT.md)（新規画像が出たら追記。**§寸法・圧縮**も必読）。
- **約 2x**: 設置ラスタは PC 表示幅の約 2 倍ピクセル。過剰は縮小、不足はアップスケールしない。
- **圧縮**: ビルド時 Vite imagemin（`config/theme-build.config.js`）。ソース配置で再圧縮しない。

## XD エクスポート固有

- 素材の置き場: **`ai-docs/xd-file/`**（このフォルダ）。`export_NNNN.json` と対応 PNG。
- **SP カンプが無い案件**では PC を正とし、既存 `mq()` で折返し・縦積み程度の SP 対応をする（作り込みすぎない）。SP カンプがある場合は PC/SP 両方を確認する。
- JSON の `text` から `fontFamily`, `fontSize`, `fontStyle`, `letterSpacing`, `fill` を取る。family / weight は [`DESIGN-TOKENS.md`](./DESIGN-TOKENS.md) と `_root` に合わせる。
- **文字色:** JSON `fill` はノード代表色のみ（文字単位色は落ちうる）。PNG は「色が分かれているか」の検知用。具体 hex はトークン照合を正とし、一致しない色だけ要確認。詳細は [`PROMPT.md`](./PROMPT.md) ステップ 3。
- **セクション背景:** アートボード塗りなし＋PNG 透明なら面を黒などで埋めない（ビューアの黒マットをデザインと誤認しない）。詳細は [`PROMPT.md`](./PROMPT.md) ステップ 7。
- `letter-spacing`: XD のトラッキング（AV）値は **値 / 1000** で `em`（例: `40` → `0.04em`）。
- **座標**: `相対 = ノード.bounds − アートボード.bounds`（絶対座標をそのまま CSS にしない）。詳細は [`PROMPT.md`](./PROMPT.md)。
- レイアウトの大枠は PNG、細部の数値は相対化した JSON `bounds` を優先。ずれを直すときは理由をコメントに残す。
- ダミー**文言**のまま実装してよい（ユーザー指示がある範囲）。ダミー**画像ファイル**での仮置きはしない（上記「画像パス」）。
- 出力先の目安: PHP テンプレート / `components/`、SCSS は `src/assets/sass/components/` または `pages/`（案件の既存構成に合わせる）。

## 出力時の注意（再掲しないが衝突しやすい点）

- コンテンツ文言の勝手な創作はしない（カンプのダミーはそのまま可）。
- `kiso.css` / `_reset.scss` / `_base.scss` 済みのスタイルをコンポーネントで重複しない（`architecture.md`）。
- PC と重複する SP 記述は書かず、差分だけ `@include mq()` に書く。
