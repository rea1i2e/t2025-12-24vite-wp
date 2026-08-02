# Coding Prompt for XD Assets（`ai-docs/xd-file/`）

XD エクスポート（JSON + PNG）を手がかりに、HTML / SCSS / PHP を更新するときの**作業手順**。  
コーディング規約の本文はここに持たない。規約は [`HTML-SCSS_PROMPT.md`](./HTML-SCSS_PROMPT.md) の「正本」を Read すること。

## 素材の置き場

- 正本ディレクトリ: **このフォルダ** `ai-docs/xd-file/`
- JSON: `export_0001.json`, `export_0002.json`, …
- PNG: `0001_001_<name>_<artboard-id>.png` など（ファイル名末尾の UUID が JSON の `artboards[].id` と一致）

## 実行指示

このファイルを読み込んだら、以下の手順を順番に実行し、該当箇所の HTML と SCSS を更新・追記してください。作業後は JSON と PNG を再確認して差異がないことを必ず検証します。

0. **デザイントークン・フォント（セクション実装の前・必須）**

   - [`DESIGN-TOKENS.md`](./DESIGN-TOKENS.md) と `src/assets/sass/base/_root.scss` を確認する。
   - トークン未記入、または `--base-font-family` がテンプレ仮フォント（例: Noto Sans JP）のままなら、**セクション実装を止めて**先にフォントを設置する。
   - 全 `export_*.json` から `fontFamily` / `fontStyle` を集計し、本文用・欧文用（数字・電話）・ウェイト対応を DESIGN-TOKENS に書く。
   - Skill `font-setup-web` + `raw/fonts/README-font-compress.md` で自己ホスト（WOFF2）。CDN 直読みは原則禁止。バリアブルがあれば優先。
   - `:root` に `--base-font-family`、必要なら `--font-family-en`。`body` は前者。数字・電話は `font-family: var(--font-family-en)`。
   - preload は FV 用 **1 ファイルのみ**（`header.php` + `ty_vite_asset_url`）。

1. **規約の正本を読む（実装前）**

   - [`HTML-SCSS_PROMPT.md`](./HTML-SCSS_PROMPT.md) の正本パスを開き、汎用 Wiki・案件 `AGENTS.md` / `architecture.md` を把握する。
   - 旧プロンプトにあった規約の再掲・推測実装はしない。

2. **対象アートボードを特定**

   - `export_0001.json` の `artboards` 配列から利用する `id` と `name` を抜き出す。
   - 同じ `id` をファイル名に含む PNG を **`ai-docs/xd-file/`** から取得して、レイアウト確認に使う。
   - これを `0001` から順番に最後まで実行する（依頼範囲があるときはその範囲のみ）。

3. **テキスト情報の抽出**

   - JSON の `text` ノードから `fontFamily`, `fontSize`, `fontStyle`, `letterSpacing`, `fill` を取る。
   - **family / weight はステップ 0 のトークン・`_root` に合わせる**（コンポーネントで別ファミリーを直書きしない。欧文・数字は `var(--font-family-en)`）。
   - XD `fontStyle` → CSS: Regular→400 / Medium→500 / Bold→700 / Black→900（トークン表を正）。
   - **文字色（重要）:** JSON の `fill` はノード代表色のみ（文字単位の色分けは export で落ちることがある。API では最後の range の色になりがち）。単色と決めつけない。
     1. 対応 PNG で「同一テキスト内に複数色があるか」を検知する
     2. 色の値は [`DESIGN-TOKENS.md`](./DESIGN-TOKENS.md) / `_root` の既知トークンに寄せる（アンチエイリアス縁の中間色を直書きしない）
     3. どのトークンにも合わない・微妙な色だけ **要確認** としてユーザーに聞く（実装で推測確定しない）
   - 見出し・ラベルなど用途ごとに分類して、クラス命名に反映。
   - 表示文言は JSON / 既存テンプレート / ユーザー指定のみ使う。カンプがダミー文言のときはそのままダミーでよい（ユーザーが「ダミーで OK」とした範囲）。

4. **レイアウト情報の整理**

   - `bounds` はアートボード原点からの**相対座標**に直して使う（下記「座標」）。
   - 画像では余白の相対感を確認、必要に応じて実寸を `sips -g pixelWidth -g pixelHeight <path>` で計測。
   - 小さめのレイアウトは「共通パーツ」と判断し、既存コンポーネント・型録を優先して再利用する。
   - 大まかなレイアウトは JSON ではなく、画像を参照して組み立てる。

5. **SP**

   - **SP カンプが無い案件**では PC を正とし、既存の `@include mq()`（768px 以下）で折返し・縦積み・余白縮小など**妥当な SP 対応**を入れる。SP 専用の見た目をカンプから推測して作り込まない。
   - SP カンプがある場合は PC/SP 両方を確認してから実装する。

6. **画像アセット**

   - アートボード PNG は見た目確認用。JSON の `image` ノードに書き出しファイルパスはほぼ無い。
   - **テンプレ同梱画像で仮埋めしない。** 命名は `{page}/{ブロック名}_{名前}`（`illust` / `thumb` 等の意味語を優先。無理なら連番。詳細は [`IMAGE-EXPORT.md`](./IMAGE-EXPORT.md)）。
   - **解像度は約 2x に揃えてから `src/assets/images/` へ置く**（表示 CSS px × 2。`cover` は枠アスペクトでクロップしてからリサイズ）。元が不足するときはアップスケールしない。手順・表は [`IMAGE-EXPORT.md` §寸法・圧縮](./IMAGE-EXPORT.md)。
   - **JPEG/PNG の品質圧縮は Vite（imagemin）に任せる。** 配置時に mozjpeg 等で潰さない。1MB ルールは正本 `coding-ejs-html.md`。
   - バリアント見た目は BEM `--modifier` ではなく `data-*`（`coding-sass.md`）。

7. **HTML と SCSS の記述**

   - 書き方は正本規約（[`HTML-SCSS_PROMPT.md`](./HTML-SCSS_PROMPT.md)）に従う。
   - 単色の `fill`・サイズ・相対 `bounds` など **1 ノード 1 値で足りるものは JSON を優先**。文字単位色・見た目の最終確認は PNG（ステップ 3）。JSON と PNG が食い違うときは PNG＋トークン照合を正とし、理由をコメントに残す。
   - **セクション背景:** アートボードに `fill` がなく PNG が透明なら、黒などで面を埋めない（ビューアの黒マットをデザイン色と誤認しない）。ページ／親の背景を透かす。面の色が必要なときだけ JSON の塗りまたはトークンで確定する。

8. **確認フロー**

   - 実装後に JSON を再チェックし、反映漏れの数値がないかをリストアップ。
   - Sass ビルドを走らせ、ブラウザで PNG と並べて見比べて差異を修正（PC 幅）。**余白に加え font-family / font-weight も照合。** SP は折返しの破綻がないかだけ確認（SP カンプが無い場合）。

9. **人間チェック前セルフQA（省略禁止）**

   - 当該セクションの実装が終わったら、ユーザーが「セルフQA不要」と明示しない限り [`../pre-human-qa-loop-prompt.md`](../pre-human-qa-loop-prompt.md) に従う。
   - 対象はこのセクションのファイルのみ。順: 規約 → デザイン再現 → **カンプ幅**（`capture-qa.config.json` の widths。SP カンプ無しなら PC のみ）。最大2周。
   - 残件を `ai-docs/fb/qa/YYYY-MM-DD-pre-human-<slug>.md` に書き、「人間チェック待ち」で止める。commit / push しない。
   - 凍結中・ペン待ちの他セクションは触らない。
   - 運用正本: ナレッジベース `wiki/ai-pre-human-qa-and-fb-rounds.md`

## 座標（相対化）

JSON の `bounds.x` / `bounds.y` は XD キャンバス上の絶対値で、アートボード原点が大きな負の数になることがある。

```text
相対 x = ノード.bounds.x - アートボード.bounds.x
相対 y = ノード.bounds.y - アートボード.bounds.y
```

余白・並びは相対値と PNG で判断する。絶対値をそのまま `left` / `top` に使わない。
