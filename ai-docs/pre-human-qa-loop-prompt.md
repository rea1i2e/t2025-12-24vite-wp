# 人間チェック前セルフQAループ（プロンプト試験用）

Skill 化前の運用用。案件の `ai-docs/` に置き、変数を埋める。

**目的:** よしあきの目視チェックの前に、AI がチェック → 修正 → 再チェック → 再修正を最大 N 周回し、残件だけ人間に渡す。

**起動:** チャットで明示しなくてもよい。`xd-file/PROMPT.md` ステップ9・`figma-design-section-prompt.md`・`AGENTS.md` により **セクション実装の完了条件**になっている（「セルフQA不要」明示時のみスキップ）。運用正本: [wiki/ai-pre-human-qa-and-fb-rounds.md](../../wiki/ai-pre-human-qa-and-fb-rounds.md)。

**見送り:** 無人 cron、自動 commit / push。短文ルール化は試験後に判断。

---

## 使い方（1案件トライアル）

1. 本ファイルを案件リポへコピーする（例: `ai-docs/pre-human-qa-loop-prompt.md`）。
2. 下の「変数」を埋める。
3. 案件リポで Agent（Cursor / Claude Code）を開き、**「実行プロンプト」全文**を投げる（または `@ai-docs/pre-human-qa-loop-prompt.md` の実行プロンプトに従って）。
4. 終了後、`ai-docs/fb/qa/` の残件 md を人間が読む。

### 前提チェック（開始前）

- [ ] `capture-qa.config.json` がある（**`widths` がカンプ幅と一致**。SP 無し案件は PC のみ）
- [ ] 対象ページのデザイン正本（Figma URL または XD export）が分かる
- [ ] ローカルで対象ページが表示できる（`baseUrl` がテンプレ名のままになっていない）
- [ ] デザイン再現を回すなら必要な MCP / 素材が使える（使えないときは軸2をスキップして報告）

---

## 変数（ここを埋める）

| 項目 | 値 |
|------|-----|
| 対象ページ（path / slug） | `REPLACE_PAGE` |
| Figma URL（PC） | `REPLACE_FIGMA_PC` |
| Figma URL（SP）※無ければ空 | `REPLACE_FIGMA_SP` |
| キャプチャ幅 | **カンプのアートボード幅に合わせる**（例: Figma 1440/393、XD 1600 のみ）。テンプレ既定を盲信しない。SP カンプが無ければ PC 幅のみ |
| 最大周数 | `2` |
| 対象ファイルの範囲 | 直近実装の当該ページ関連のみ（例: `src/.../ページ`） |
| 量変化チェック | `off`（初回試験は off 推奨。on なら方針を1行） |

---

## 実行プロンプト（このブロックを Agent に渡す）

```text
あなたは実装者兼セルフQA担当。よしあきの人間チェックの前に、次のセルフQAループを回す。

## 対象
- ページ: REPLACE_PAGE
- デザイン正本: REPLACE_DESIGN（Figma URL または XD export）
- キャプチャ幅: REPLACE_WIDTHS（カンプ一致。SP 無しなら PC のみ）
- 触ってよい範囲: 当該ページの実装に必要なファイルのみ（無関係なリファクタ禁止）
- 最大周数: 2
- 量変化チェック: off

## 対応順（この順のみ。飛ばしたら理由を残件に書く）
1. 規約の遵守 — skills/ai-code-convention-fix と checklist.md（ナレッジベース）
2. デザインの再現 — 可能なら capture-qa（カンプ幅）→ デザイン比較。不可ならスクショ＋目視差分の箇条書き
3. 画面幅 — **カンプがある幅**で照合。SP カンプが無い案件は狭い幅をデザイン major にしない（折返し破綻のスモークのみ可）
4. テキスト量・要素量が変わっても崩れない — 今回は off（疑わしい固定高 / absolute があれば残件に「疑い」だけ列挙）

## 1周の手順
A. Check — 問題を major / minor / 要確認 で箇条書き（捏造しない。不明は要確認）
B. Fix — その周の major だけ直す。コンテンツ文言は捏造しない
C. Recheck — 直した軸だけ再確認（規約なら再読、見た目なら再キャプチャ）
D. 記録 — 直したこと / 残ったことを短く残す

## 停止条件（どれかで止めて人間へ）
- major がゼロになった
- 最大周数に達した
- 残件が「要確認」と minor だけになった
- Figma / ローカルが使えず軸2以降を回せない（その時点で報告）

## 禁止
- git commit / push
- スコープ外ファイルの整理
- 「たぶん直った」で major を消すこと（再チェック根拠がない修正完了は不可）
- 量変化用のダミー長文を本番コンテンツとして残すこと

## 終了時の成果物（必須）
1. チャットで要約: 何周したか / 直した major / 残 major・要確認
2. 案件に残件ファイルを書く:
   ai-docs/fb/qa/YYYY-MM-DD-pre-human-<slug>.md
   （無ければ ai-docs/fb/qa/ を作り、_template があれば踏襲）
   含める見出し:
   - 実施した軸と周数
   - 修正済み（ファイルパス付き）
   - 人間向け残件（major / minor / 要確認）
   - 次に人間が見るべき画面（URL・幅）

終わったら「人間チェック待ち」と明示して停止する。追加の自主改修はしない。
```

---

## 短い起動文（変数を埋めた md があるとき）

```text
@ai-docs/pre-human-qa-loop-prompt.md の実行プロンプトに従い、セルフQAループを回して。
コミットしない。終わったら fb/qa の残件 md を書いて人間チェック待ちで止めて。
```

---

## 試験後にメモする観点（Skill 化判断用）

トライアル後、次を1行ずつ残すとよい（日報 or 案件メモ）。

- 何周で major が潰れたか（または残ったか）
- 人間が追加で直した量（多ければプロンプト不足／軸の判定が甘い）
- トークン・待ち時間は許容か
- Skill 化するか、プロンプトのまま案件テンプレに同梱するか

---

## 関連

- **運用の正本（4軸・再FB上限・AI/手動）:** [wiki/ai-pre-human-qa-and-fb-rounds.md](../../wiki/ai-pre-human-qa-and-fb-rounds.md)
- qaFB 対応順: 規約 → デザイン再現 → 幅 → 量
- [`skills/ai-code-convention-fix/SKILL.md`](../../skills/ai-code-convention-fix/SKILL.md)
- [`skills/figma-qa-compare/SKILL.md`](../../skills/figma-qa-compare/SKILL.md)
- [`skills/capture-qa/SKILL.md`](../../skills/capture-qa/SKILL.md)
- Notion フェーズ3: [wiki/ai-security-adoption-checklist.md](../../wiki/ai-security-adoption-checklist.md)（本試験は Skill 化前のプロンプト運用）
