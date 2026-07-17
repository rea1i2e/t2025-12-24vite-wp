# ai-docs 索引（WP テンプレ）

**技術ドキュメントの正本**（設計・導入・開発・デプロイ・トラブル・コーディング規約）と、案件複製後の **AI 向け追記**を置く。人間向け入口はルート [`README.md`](../README.md)。

## なぜ `docs/` ではなく `ai-docs/` か

- 案件サイトに **`/docs/` URL** や **`docs/` ディレクトリ**があることが多く、リポ内の「docs」と**名前がかぶって曖昧**になりやすい。
- 開発用 md は **FTP・納品対象外**と明示しやすい名前にする（[`legacy-wp-diff-export`](/Users/yoshiaki/working/2026-04-23kn/skills/legacy-wp-diff-export/SKILL.md) 既定除外と同型）。

詳細: ナレッジ [`wiki/template-repository-docs.md`](/Users/yoshiaki/working/2026-04-23kn/wiki/template-repository-docs.md)

## 正本（テンプレ共通）

| ファイル | 内容 |
|---|---|
| [`architecture.md`](architecture.md) | スタック固有の技術正本（導入・設計・開発・デプロイ・トラブル・WP 固有コーディング規約） |

リポ固有の意思決定（ADR）が増えたら **`ai-docs/decisions/`** に置いてよい。**ADR の書き方・命名**はナレッジ [adr-workflow.md](/Users/yoshiaki/working/2026-04-23kn/wiki/adr-workflow.md)。WP テンプレの記録集約: [wp-template-decision-records.md](/Users/yoshiaki/working/2026-04-23kn/wiki/wp-template-decision-records.md)。

## 案件複製後に追記する例

| ファイル（例） | 用途 |
|---|---|
| [`figma-design-mapping.md`](figma-design-mapping.md) | Figma URL 正本（ページ・node・実装ファイル対応） |
| [`figma-design-kickoff-prompt.md`](figma-design-kickoff-prompt.md) | 案件開始時：URL を mapping にストック |
| [`figma-design-section-prompt.md`](figma-design-section-prompt.md) | 1セクションずつ Figma MCP → 実装 |
| [`fb.md`](fb.md) | 修正指示の一括リスト（1ラリーで直す） |
| `scss-coding-rules.md` | レガシーテーマ改修の Sass 規約・地雷 |
| `path-conventions.md` | 画像パス・enqueue の案件固有ルール |
| `css-loading-order.md` | 既存 CSS 積層の調査結果 |
| `skills/*.md` | 案件専用手順（正本化したらナレッジ `skills/` へ） |

## トリガー（案件で増やしたとき）

| 作業 | 読むファイル |
|---|---|
| スタック共通・セットアップ | [`architecture.md`](architecture.md) |
| Figma 初回受領・mapping 整備 | [`figma-design-kickoff-prompt.md`](figma-design-kickoff-prompt.md) → [`figma-design-mapping.md`](figma-design-mapping.md) |
| Figma から1セクション実装 | [`figma-design-section-prompt.md`](figma-design-section-prompt.md) + [`figma-design-mapping.md`](figma-design-mapping.md) |
| 修正 FB をまとめて1回 | [`fb.md`](fb.md)（`figma-qa-compare` の修正依頼文を転記可） |
| （案件で追加したルール） | 下表を更新 |

| ファイル | 要約 | 最終更新 |
|---|---|---|
| [`architecture.md`](architecture.md) | テンプレ技術正本 | — |
| [`session-handoff.md`](session-handoff.md) | Cursor ↔ Claude Code 引き継ぎ | 2026-07-06 |
| [`claude-code-resume-prompt.md`](claude-code-resume-prompt.md) | Cursor → Claude Code 再開用コピペ | 2026-07-06 |
| [`figma-design-mapping.md`](figma-design-mapping.md) | Figma URL 正本（雛形） | 2026-07-07 |
| [`figma-design-kickoff-prompt.md`](figma-design-kickoff-prompt.md) | 案件開始：mapping ストック | 2026-07-07 |
| [`figma-design-section-prompt.md`](figma-design-section-prompt.md) | 1セクション実装プロンプト | 2026-07-07 |
| [`fb.md`](fb.md) | 修正 FB 一括指示 | 2026-07-17 |

**メンテ:** ファイル追加・改訂時はこの索引表も更新する。
