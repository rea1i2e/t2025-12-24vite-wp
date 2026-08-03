# XD デザインカンプ — ローカル入口（WP テンプレ）

XD JSON / PNG から HTML+SCSS（WordPress テーマ）を書くときの**このリポの入口**。  
**コーディング規約の本文はここに持たない。**

## 汎用の正本（必読）

`/Users/yoshiaki/working/2026-04-23kn/wiki/camp-html-scss-implementation.md`

実装前に必ず Read すること（規約ポインタ・Sass/画像/XD 共通）。

## このリポの入口

| 内容 | パス |
|------|------|
| 案件入口 | [`AGENTS.md`](../../AGENTS.md) |
| WP テンプレ固有 | [`ai-docs/architecture.md`](../architecture.md) |
| デザイントークン・レイアウト幅 | [`DESIGN-TOKENS.md`](./DESIGN-TOKENS.md) |
| 画像書き出し一覧 | [`IMAGE-EXPORT.md`](./IMAGE-EXPORT.md) |
| 作業手順（アートボード順・検証） | [`PROMPT.md`](./PROMPT.md) |
| PHP 出力エスケープ | [`.cursor/rules/php-escape-output.mdc`](../../.cursor/rules/php-escape-output.mdc) |
| Sass mixin | [`.cursor/rules/sass-use-mixins.mdc`](../../.cursor/rules/sass-use-mixins.mdc) |

**ステップ 0（フォント・レイアウト幅）を飛ばさない**（`PROMPT.md`）。

## このテンプレの差分

- **スタック:** WordPress。画像は `ty_img()` / `ty_theme_image_url()`。出力は PHP テンプレート / `components/`、SCSS は `src/assets/sass/components/` 等。
- **SP カンプが無い案件**では PC を正とし、`mq()` で折返し・縦積み程度。SP がある場合は PC/SP 両方。
- XD 共通事項はナレッジ正本を参照。手順の詳細は [`PROMPT.md`](./PROMPT.md)。
