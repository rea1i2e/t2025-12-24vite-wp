# Figma 1セクション実装プロンプト

[`figma-design-mapping.md`](figma-design-mapping.md) 整備後の **コーディング用** テンプレ。  
**1セクションずつ** Figma MCP で取得 → 実装する（ページ一括は精度低下のため禁止）。

## プロンプト（コピペ用）

```
`ai-docs/figma-design-mapping.md` の **{ページ名}**（`/{path}/`）を実装してください。

## 対象

- ページ: `/{path}/` — {ページ名}
- 今回のセクション: {例: FV / 特徴 / CTA / フッター手前}
- mapping 節の Figma URL（PC）: （mapping からコピー）
- mapping 節の Figma URL（SP）: （あればコピー）

## 手順

1. mapping の URL から **上記1セクションのみ** Figma MCP（get_design_context / get_screenshot）でデザインを取得する
2. 実装前に `mockup-to-existing-page` Skill に従い、対象 PHP テンプレ・既存 SCSS・reset/base/root を読む
3. 取得したデザインを既存設計に合わせ **最小差分** で実装する
4. このセクションだけ完了したら止める。次セクションは別依頼

## 実装ファイル（mapping より）

- （mapping の「実装ファイル」節をコピー）

## 禁止

- ページ全体の一括コーディング
- mapping に無い Figma URL を勝手に使う（必要なら mapping を先に更新）
- PHP/HTML のテキストコンテンツを自動生成（既存 or ユーザー提供を正とする）

## 完了後

- mapping の「セクション索引」表があれば実装済を更新
- 修正 FB はまとめて1回（[`fb.md`](fb.md) に溜めてから一括指示）
```

---

## セクションの切り方（目安）

| 優先 | 単位 | 例 |
|---|---|---|
| 高 | ページ内の独立ブロック | FV、特徴3カラム、CTA |
| 中 | 再利用パーツ | カード、ボタン行 |
| 低 | ページ全体 | 精度低下のため避ける |

---

## 関連

- [`figma-design-kickoff-prompt.md`](figma-design-kickoff-prompt.md) — mapping 初回整備
- ナレッジ `skills/mockup-to-existing-page/SKILL.md`
- ナレッジ `skills/figma-qa-compare/SKILL.md` — 初稿前 QA
