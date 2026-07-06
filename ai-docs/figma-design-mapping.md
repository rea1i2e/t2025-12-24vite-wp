# Figma デザイン対応表

Figmaファイル（現行）: `{ファイル名}`  
ファイルキー: `{fileKey}`

> **運用:** このファイルが案件の **Figma 正本**。URL はプロジェクト開始時に1回 mapping へストックし、以降はここを参照する（再共有不要）。  
> **正本 Figma の複製差し替え**（fileKey 変更・node-id 再特定）: ナレッジ [`skills/figma-design-refresh-pixexport/SKILL.md`](/Users/yoshiaki/working/2026-04-23kn/skills/figma-design-refresh-pixexport/SKILL.md)  
> **ピクパ設定（任意）:** `figma-pixexport.config.json` — 例は [`figma-pixexport.config.example.json`](/Users/yoshiaki/working/2026-04-23kn/skills/figma-design-refresh-pixexport/figma-pixexport.config.example.json)  
> **実案件の参考:** クリクラ `ai-docs/figma-design-mapping.md`（2026-06-10yk）

---

## ページ別マッピング

<!-- 案件着手時: 下記を複製してページを追加。node-id は Figma URL の `node-id=` を `-` 表記で書く -->

### `/{path}/` — {ページ名}

- **Figmaノード（PC）:** `{xxxx-xxxx}`
- **Figma URL（PC）:** https://www.figma.com/design/{fileKey}/{fileName}?node-id={xxxx-xxxx}&m=dev
- **Figmaノード（SP）:** `{xxxx-xxxx}` （PC のみの場合は省略）
- **Figma URL（SP）:** https://www.figma.com/design/{fileKey}/{fileName}?node-id={xxxx-xxxx}&m=dev
- **実装ファイル:**
  - `page-{slug}.php`（または該当 PHP テンプレ）
  - `assets/scss/object/project/_p-{name}.scss`
  - `assets/js/`（必要なら）
- **注意:** （構造差・既存流用・`$is_plan_renew` 等。任意）
- **plans（任意）:** `ai-docs/plans/page-{slug}-plan.md` — 複雑ページの型・地雷メモ

---

### `/{path}/` — {2ページ目}

- **Figmaノード:** `{xxxx-xxxx}`
- **Figma URL:** https://www.figma.com/design/{fileKey}/…?node-id={xxxx-xxxx}&m=dev
- **実装ファイル:**
  - （上と同型）
- **注意:**

---

## セクション索引（任意）

1セクションずつ実装するときの進捗メモ。Figma 上のフレーム名と対応させる。

| ページ | セクション | Figma フレーム名 | 実装済 |
|---|---|---|---|
| `/{path}/` | FV | Hero | |
| `/{path}/` | 特徴 | Features | |
| `/{path}/` | CTA | CTA | |

---

## 更新履歴

| 日付 | 内容 |
|---|---|
| YYYY-MM-DD | 初版（fileKey・ページ mapping 作成） |
