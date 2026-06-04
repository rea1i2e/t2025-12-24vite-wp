# AIエージェント向けプロジェクト理解ガイド

このドキュメントは、AIエージェント（Cursor / ChatGPT）がこのプロジェクトを理解し、適切なコード変更や提案を行うためのガイドです。

対象は **WordPress サイト制作用テンプレート**（略称 **WP テンプレ**）。呼称の正本はナレッジベースの [wiki/operated-repositories.md](/Users/yoshiaki/working/2026-04-23kn/wiki/operated-repositories.md#表記ルール3-リポジトリと型録)（表記ルール）。

**人間が読む場合は、ルート [README.md](README.md) と [docs/architecture.md](docs/architecture.md) を参照してください。**

---

## ローカル絶対パス（個人環境・Cursor 用）

複製テンプレ運用で、エージェントが `@` 参照やファイル読み込みに使う。`npm run init` 等でデモを消した案件でも、JS（タブ・スライダー・モーダル等）の**型録**は**静的テンプレ**側を参照できる。共有マシン・リモートではパスが一致しない。テーマを Local 上で別名にした場合は下表の「WP テンプレ」を更新すること。

| 役割 | パス |
|------|------|
| WP テンプレ（本リポジトリ・この WordPress テーマ） | `/Users/yoshiaki/Local Sites/t2025-12-24vite-wp/app/public/wp-content/themes/t2025-12-24vite-wp` |
| 静的テンプレ（型録・EJS / デモの参照先。`{型録}` はこのルートを指す） | `/Users/yoshiaki/working/t2025-10-01vite` |
| ナレッジベース（コーディング規約の汎用正本・`wiki`） | `/Users/yoshiaki/working/2026-04-23kn` |

### 案件ナレッジ（stock）

- **実装・コミット**はこのリポジトリ（WP テンプレ）で行う。
- **案件ナレッジの md** の置き方・書式の目安は、ナレッジベースの [`/Users/yoshiaki/working/2026-04-23kn/wiki/stock-format.md`](/Users/yoshiaki/working/2026-04-23kn/wiki/stock-format.md) に従う（`wiki` 上に案件用ページを切る、または `raw/`・案件リポ等）。**このリポジトリ内に `stock/` や案件メモ専用の md を新設しない**。
- Cursor では、必要に応じてナレッジベースをマルチルートで開くか、チャットにその `AGENTS.md` または `wiki/stock-format.md` を添付する。

### 動画の Web 向け圧縮（エージェント）

- **手順の Skill（正本）**: `/Users/yoshiaki/working/2026-04-23kn/.cursor/skills/video-compress-web/SKILL.md`（ナレッジをワークスペースに含めない場合は `~/.cursor/skills/` へ同内容を置いてもよい）
- **`raw/videos/` の正本**: **静的テンプレ（型録）のみ**。本テーマで圧縮フローを使うときは、**型録から `raw/videos/` を手動で丸ごとコピー**し、テーマルートに置く。`package.json` に `inspect:video` / `compress:video` を型録と同じ内容で追加する。手順の細部は `{型録}/raw/videos/README-video-compress.md` の「WordPress・案件リポで使う場合」を参照。
- **未複製のとき**: 型録のルートで圧縮し、出力だけを `src/assets/videos/` 等へ取り込む。
- **技術手順の正本**: `{型録}/raw/videos/README-video-compress.md`（チャット依頼は型録の README「動画圧縮を AI に依頼するとき」を参照）
- **索引**: ナレッジ `wiki/asset-compression-notes.md`

### Web フォントの設置・圧縮（エージェント）

- **手順の Skill（正本）**: `/Users/yoshiaki/working/2026-04-23kn/.cursor/skills/font-setup-web/SKILL.md`（ナレッジをワークスペースに含めない場合は `~/.cursor/skills/` へ同内容を置いてもよい）
- **方針の正本**: ナレッジ `wiki/web-fonts-guidelines.md`
- **圧縮コマンドの正本**: 本テーマ **`raw/fonts/README-font-compress.md`**（静的テンプレと**同じディレクトリ構成・手順**。`font-compress.sh` / `font-compress-subset.sh`）
- **反映先**: `src/assets/sass/base/_root.scss`、`header.php` の preload（`ty_vite_asset_url('src/assets/fonts/...')`）

### インタラクション実装時の型録参照（必須）

**`{型録}`** — **静的テンプレのルートディレクトリ**を指すプレースホルダ。ローカルでは下記の絶対パスと同一（環境ごとに異なる。定義は [operated-repositories.md](/Users/yoshiaki/working/2026-04-23kn/wiki/operated-repositories.md)）。

ユーザーが **短い指示だけ**（例:「タブ切り替えを実装して」「スライダーを付けて」「モーダルにして」）で依頼した場合も、**WP テンプレ内に該当コードが無い・`npm run init` 後でデモが無いときは、実装・提案に着手する前に** **静的テンプレ（型録）** を読むこと。

- **`{型録}` の例（ローカル絶対パス）**: `/Users/yoshiaki/working/t2025-10-01vite`
- パス表記では `{型録}` と **静的テンプレのルート** を結合する（例: `{型録}/src/assets/js/demo/_tab.js`）。

**手順（この順で行う）**

1. 下表から **JS・マークアップ（EJS）・Sass** の該当ファイルパスを特定し、`Read` 等で内容を把握する。
2. マークアップの組み立て例が必要なら、デモページの `index.html` を読む（どの `_p-*.ejs` が載っているかの索引になる）。
3. この WP テンプレ向けに **PHP テンプレート・既存の BEM / `ty_` 規約・[docs/architecture.md のコーディング規約節](docs/architecture.md#コーディング規約wp-テンプレ固有)** に合わせて移植する。EJS のままコピーしない。**コンテンツ文言の捏造は禁止**（汎用ラベルのみ・またはプレースホルダにし、ユーザーまたは既存データに任せる）。
4. JS は静的テンプレ（型録）の **`data-*` / `.js-*` クラス契約と同等の DOM 構造**を保てるならそのまま近い形で取り込み、テーマのエントリ（例: `main.js`）に import する必要があれば `{型録}/src/assets/js/main.js` を参照する。

**主要パーツと型録ファイル（`{型録}/` 以下）**

| 機能 | JS（優先） | マークアップ（EJS） | Sass | デモページ（索引） |
|------|------------|---------------------|------|-------------------|
| タブ切り替え | `src/assets/js/demo/_tab.js` | `src/ejs/components-demo/_p-tab.ejs` | `src/assets/sass/demo-components/_p-tab.scss` | `src/demo/demo-tab/index.html` |
| アコーディオン | `src/assets/js/demo/_accordion.js` | `src/ejs/components-demo/_p-accordions.ejs` | `src/assets/sass/demo-components/_p-accordions.scss` | `src/demo/demo-accordion/index.html` |
| モーダル（`dialog` 系） | `src/assets/js/demo/_dialog-general.js`、`_dialog-youtube.js`、`_dialog-video.js`（共通: `_dialog-common.js`） | `src/ejs/components-demo/_p-dialog*.ejs`（トリガー・本体・動画用） | `_p-dialog.scss`、`_p-dialog-trigger.scss`、`_p-dialog-trigger-youtube.scss`、`_p-dialog-trigger-video.scss` | `src/demo/demo-dialog/index.html` |
| モーダル（`dialog` 以外） | `src/assets/js/demo/_modal.js` | 上記と用途に応じて `components-demo` 内を検索 | 同上または近傍 | `src/demo/demo-dialog/index.html` を参考 |
| スライダー（Splide） | `src/assets/js/demo/_splide-fade.js` ほか `_splide-loop.js`、`_splide-thumbnail.js`、`_splide-progress.js`、`_splide-posts.js` | 同名の `src/ejs/components-demo/_p-splide-*.ejs` | 同名の `src/assets/sass/demo-components/_p-splide-*.scss` | `src/demo/demo-splide/index.html` |

### アクセシビリティ仮基準（参照）

- **正本（共通）:** ナレッジベース `/Users/yoshiaki/working/2026-04-23kn/wiki/a11y-baseline.md`（**Must / Should / 運用 / チェックリスト**）。**基準の改訂はこの Wiki で行う。**
- **WP テンプレでの実装の手がかり:** [docs/architecture.md](docs/architecture.md) 冒頭の A11y 補足、[コーディング規約（WP テンプレ固有）](docs/architecture.md#コーディング規約wp-テンプレ固有)、および本ファイルの「型録参照」（stub ファイルは置かない）。
- **適用**は**静的サイトに限らない**（WCAG 適合の宣言文書ではない）。PHP・テンプレート・JS の**新規・修正**の際、Wiki 正本の **Must** を当該範囲で満たす。

表に無いパターン（トグル・フェードイン等）のときは、**`{型録}/src/assets/js/main.js` の `import './demo/...'` を一覧し**、対応する `src/ejs/components-demo/`・`src/assets/sass/demo-components/` を `grep` で辿る。

---

## 技術スタック

- **WordPress**: クラシックテーマ（PHPテンプレート）
- **Vite**: フロントエンドビルドツール（HMR対応）
- **Sass**: CSSプリプロセッサ
- **PostCSS**: CSS後処理（autoprefixer、メディアクエリソート）

---

## コーディングルール

### 汎用ルール（WordPress・静的コーディング共通）

ナレッジベースの `/Users/yoshiaki/working/2026-04-23kn/wiki/coding-conventions.md` を入口に、`wiki/coding-*.md` を参照すること。旧 `2026-03-20kn/coding-rules/` は**廃止**した。

| ページ | 内容 |
|---|---|
| [`wiki/coding-common.md`](/Users/yoshiaki/working/2026-04-23kn/wiki/coding-common.md) | 実装方針・判断基準 |
| [`wiki/coding-php.md`](/Users/yoshiaki/working/2026-04-23kn/wiki/coding-php.md) | PHP |
| [`wiki/coding-ejs-html.md`](/Users/yoshiaki/working/2026-04-23kn/wiki/coding-ejs-html.md) | HTML 構造・クラス名・考え方 |
| [`wiki/coding-sass.md`](/Users/yoshiaki/working/2026-04-23kn/wiki/coding-sass.md) | Sass/SCSS |
| [`wiki/coding-javascript.md`](/Users/yoshiaki/working/2026-04-23kn/wiki/coding-javascript.md) | JavaScript |

### WP テンプレ固有のルール

- **[docs/architecture.md](docs/architecture.md)**: 設計判断・コーディング（WP 固有）・開発・デプロイ・トラブル等の**技術正本**

---

## アーキテクチャ概要

詳細は [docs/architecture.md](docs/architecture.md) を参照してください。

### 開発環境（dev）のフロー

```
ブラウザ → WordPressページリクエスト
    ↓
WP_Theme_PHP（header.php, functions.php など）
    ↓
load_assets（アセット読み込み指示）
    ↓
Vite_dev_server(HMR) ← localhost:5173 から CSS/JS を取得
```

### 本番環境（prod）のフロー

```
npm run build
    ↓
vite build
    ↓
dist/ にハッシュ付きアセットを出力
    ├─ assets/js/main-*.js
    ├─ assets/css/style-*.css
    └─ .vite/manifest.json（パスマッピング）
    ↓
WP_Theme_PHP
    ↓
read_manifest（dist/.vite/manifest.json を読み込み）
    ↓
dist/ から実際のファイルを enqueue
```

---

## Git（エージェント向け）

- **`commit` / `push` / ブランチ操作 / マージ / `stash` など、リポジトリに履歴やリモートへ影響する Git 操作は、ユーザーから明示的に依頼があった場合のみ行う。**
- 作業完了・ドキュメント更新・整理を理由に、依頼なく自動でコミットしない。
- コミットが必要そうなときは、変更内容を説明したうえでユーザーにコミット・push を依頼するか確認する。
- コミットメッセージは日本語でよい（`.cursorrules` 参照）。

---

## 変更時の注意点

- **WP テンプレビルド:** [`config/theme-build.config.js`](config/theme-build.config.js) が正本（既定 `imageAltFormats: "none"`・`useFileHash: true`・`cssMinify: true`）。案件複製後は案件ごとに上書き（例: ik は `avif` + `useFileHash: false`）。詳細は [docs/architecture.md](docs/architecture.md) の画像最適化節。
- **Vite連携の仕組みを変更しない**: dev/prod判定とmanifest読み込みの仕組みは必須
- **関数名のプレフィックス**: `ty_` を必ず付与（WordPress/プラグインとの衝突回避）
- **画像パスの解決**: CSS内はViteが解決、HTML内は `ty_theme_image_url()` を使用

---

## ドキュメント（更新時）

**いつ・何を文書化するか、ADR の基準**はナレッジ [template-repository-docs.md](/Users/yoshiaki/working/2026-04-23kn/wiki/template-repository-docs.md) を参照。

**このリポジトリでの行き先（WP テンプレ固有）**

| 内容 | 行き先 |
|------|--------|
| 技術仕様・手順の追記 | [docs/architecture.md](docs/architecture.md) の該当節 |
| 意思決定 | ナレッジ [wp-template-decision-records.md](/Users/yoshiaki/working/2026-04-23kn/wiki/wp-template-decision-records.md)・[adr-workflow.md](/Users/yoshiaki/working/2026-04-23kn/wiki/adr-workflow.md)／新規 ADR は必要なら `docs/decisions/NNNN-topic.md` |
| A11y の実装の手がかり | [docs/architecture.md](docs/architecture.md) 冒頭・コーディング規約節／Wiki 正本 `wiki/a11y-baseline.md` |

---

## 参考ドキュメント

- **技術正本**: [docs/architecture.md](docs/architecture.md)
