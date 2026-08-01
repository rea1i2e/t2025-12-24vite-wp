# 案件着手 — デモ／仮アセット一掃プロンプト

案件リポで Figma／コーディングを始める**前に 1 回**使う。方針の正本はナレッジ [`wiki/wp-template-case-init.md`](/Users/yoshiaki/working/2026-04-23kn/wiki/wp-template-case-init.md)。

**テンプレ本体では実行しない**（デモカタログが消える）。

## プロンプト（コピペ用）

```
この案件は WP テンプレ複製直後（またはデモが残ったまま）です。
ナレッジ `wiki/wp-template-case-init.md` の方針でデモ・仮アセットを一掃してください。

## 方針（要約）

- 必ず使う土台以外は削除する
- 「後で使うかも」で保険残ししない
- 必要なパーツは案件に復元せず、WP テンプレ原本または静的テンプレ（型録）から都度移植する
- WP テンプレ本体では npm run init を実行しない

## 依頼

1. 案件リポに `npm run init`（`scripts/init-project.sh`）があるか確認する。無ければ WP テンプレ正本から脚本と package.json の `init`・AGENTS の「案件着手時のデモ削除」を還流してから実行する
2. init を実行する（または脚本が古い場合は wiki の削除リストに合わせて手動で同等にする）
3. 特に次を残さないこと:
   - components-demo / sass/components-demo
   - images/demo / images/common（仮アイコン・仮ロゴ）
   - demo 向け JS と main.js の当該 import
   - func-set-posttype-works / func-posts-ajax-load-more 等の任意サンプル
   - get_template_part('components-demo/...')
4. images/common を消すときは Sass の url(.../images/common/...) もコメントアウトまたは除去する（ビルド切れ防止）
5. rg と npm run build で確認する
6. 実装はまだ始めない（掃除と確認だけ）。次の実装は型録参照＋ Figma mapping から

## 参照

- ナレッジ `wiki/wp-template-case-init.md`
- 案件 `AGENTS.md`「案件着手時のデモ削除」「型録参照」
- WP テンプレ `scripts/init-project.sh`
```

## 完了条件

- [ ] `npm run init`（または同等）済み
- [ ] `components-demo`・仮画像ディレクトリが無い
- [ ] `npm run build` が通る
