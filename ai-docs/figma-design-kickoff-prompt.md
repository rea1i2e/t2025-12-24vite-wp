# Figma デザイン開始プロンプト（案件キックオフ）

案件で Figma URL を初めて受け取ったときに **1回だけ** 実行する。以降は [`figma-design-mapping.md`](figma-design-mapping.md) を正本とし、URL の再共有は不要。

## プロンプト（コピペ用）

```
以下の Figma をこの案件の正本として `ai-docs/figma-design-mapping.md` に整理してください。

## Figma URL

- （案件の Figma ファイル URL、またはページ単位 URL をここに貼る）

## 依頼

1. 各ページの PC/SP node-id と dev mode URL を mapping の「ページ別マッピング」節に追記する
2. 実装ファイル（PHP テンプレ・SCSS・JS）の見込みパスを各節に書く
3. 正本 fileKey・ファイル名を mapping 冒頭に記載する
4. 複雑ページは `ai-docs/plans/page-{slug}-plan.md` の要否を判断し、必要なら雛形を作る
5. セクションが多いページは「セクション索引」表を追加する
6. 以降、ユーザーが Figma URL を再共有しなくてよい状態にする

## 参照

- `ai-docs/figma-design-mapping.md`（雛形）
- `ai-docs/figma-design-section-prompt.md`（1セクション実装時）
- ナレッジ `skills/mockup-to-existing-page/SKILL.md`（既存ページへの反映時）
- ナレッジ `skills/figma-design-refresh-pixexport/SKILL.md`（正本 Figma 差し替え時）

## 禁止

- mapping を整えずにページ全体の一括コーディングを始める
```

---

## 完了条件

- [ ] `figma-design-mapping.md` に fileKey と対象ページの URL が揃っている
- [ ] 各ページ節に実装ファイルの見込みパスがある
- [ ] 次の実装依頼は [`figma-design-section-prompt.md`](figma-design-section-prompt.md) から始められる

---

## 関連

- ナレッジ [`wiki/ai-security-adoption-checklist.md`](/Users/yoshiaki/working/2026-04-23kn/wiki/ai-security-adoption-checklist.md) フェーズ2
- Notion TODO: AI活用_FigmaのURLを1度渡せば済む状態を作る（ひろむさん）
