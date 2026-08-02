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

1. `ai-docs/xd-file/DESIGN-TOKENS.md` と `_root.scss` を確認。仮フォントのままなら **セクション実装を止め**、kickoff / `font-setup-web` で先に揃える
2. mapping の URL から **上記1セクションのみ** Figma MCP（get_design_context / get_screenshot）でデザインを取得する
3. 実装前に `mockup-to-existing-page` Skill に従い、対象 PHP テンプレ・既存 SCSS・reset/base/root を読む
4. 取得したデザインを既存設計に合わせ **最小差分** で実装する（family / weight は DESIGN-TOKENS と `_root` に合わせる）
5. 実装後、ユーザーが「セルフQA不要」と明示しない限り `ai-docs/pre-human-qa-loop-prompt.md` で**このセクションだけ**セルフQA（最大2周）→ `fb/qa` 残件 →「人間チェック待ち」で止める
6. 次セクションは別依頼（このセクションのセルフQAまでが1単位）

## 実装ファイル（mapping より）

- （mapping の「実装ファイル」節をコピー）

## 禁止

- ページ全体の一括コーディング
- mapping に無い Figma URL を勝手に使う（必要なら mapping を先に更新）
- テンプレ仮フォントのまま実装を進める
- PHP/HTML のテキストコンテンツを自動生成（既存 or ユーザー提供を正とする）

## 完了後（必須）

1. mapping の「セクション索引」表があれば実装済を更新
2. **人間チェック前セルフQA（省略禁止）** — ユーザーが「セルフQA不要」と明示しない限り [`pre-human-qa-loop-prompt.md`](pre-human-qa-loop-prompt.md) に従い、**このセクション範囲だけ**最大2周（規約 → デザイン再現 → カンプ幅）。残件を `fb/qa/` に書いて「人間チェック待ち」で止める。commit / push しない。凍結中の他セクションは触らない
3. 人間からの修正 FB は種類別に [`fb/`](fb/README.md) へ（デザイン差分は `fb/qa/`。原因解明→修正→ブラウザ確認）

運用正本: ナレッジ `wiki/ai-pre-human-qa-and-fb-rounds.md`


---

## セクションの切り方（目安）

| 優先 | 単位 | 例 |
|---|---|---|
| 高 | ページ内の独立ブロック | FV、特徴3カラム、CTA |
| 中 | 再利用パーツ | カード、ボタン行 |
| 低 | ページ全体 | 精度低下のため避ける |

---

## 関連

- [`pre-human-qa-loop-prompt.md`](pre-human-qa-loop-prompt.md) — セクション実装後のセルフQA
- [`figma-design-kickoff-prompt.md`](figma-design-kickoff-prompt.md) — mapping 初回整備
- [`xd-file/DESIGN-TOKENS.md`](xd-file/DESIGN-TOKENS.md) — フォント等トークン
- ナレッジ `skills/font-setup-web/SKILL.md`
- ナレッジ `skills/mockup-to-existing-page/SKILL.md`
- ナレッジ `skills/figma-qa-compare/SKILL.md` — 初稿前 QA
