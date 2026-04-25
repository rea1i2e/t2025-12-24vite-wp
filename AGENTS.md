# AIエージェント向けプロジェクト理解ガイド

このドキュメントは、AIエージェント（Cursor / ChatGPT）がこのプロジェクトを理解し、適切なコード変更や提案を行うためのガイドです。

**人間が読む場合は、[docs/01-development/README.md](docs/01-development/README.md) を参照してください。**

---

## ローカル絶対パス（個人環境・Cursor 用）

複製テンプレ運用で、エージェントが `@` 参照やファイル読み込みに使う。`npm run init` 等でデモを消した案件でも、JS（タブ・スライダー・モーダル等）の型録は静的テンプレ側を参照できる。共有マシン・リモートではパスが一致しない。テーマを Local 上で別名にした場合は下表の「本リポジトリ」を更新すること。

| 役割 | パス |
|------|------|
| 本リポジトリ（この WordPress テーマ） | `/Users/yoshiaki/Local Sites/t2025-12-24vite-wp/app/public/wp-content/themes/t2025-12-24vite-wp` |
| 静的 Vite テンプレ（EJS・デモ削除後の参照先） | `/Users/yoshiaki/working/t2025-10-01vite` |
| ナレッジ用リポジトリ（コーディングルール・`stock/`） | `/Users/yoshiaki/working/2026-03-20kn` |

### 案件ナレッジ（stock）

- **実装・コミット**はこのリポジトリ（本テーマ）で行う。
- **案件ナレッジの md** はナレッジ用リポの `stock/` にだけ追加・更新する。書式は [`/Users/yoshiaki/working/2026-03-20kn/formats/stock-format.md`](/Users/yoshiaki/working/2026-03-20kn/formats/stock-format.md) に従う。**このテーマ内に `stock/` や案件メモ専用の md を新設しない**。
- Cursor では、必要に応じてナレッジ用リポをマルチルートで開くか、チャットにそのリポの `AGENTS.md` または `formats/stock-format.md` を添付する。

### インタラクション実装時の型録参照（必須）

ユーザーが **短い指示だけ**（例:「タブ切り替えを実装して」「スライダーを付けて」「モーダルにして」）で依頼した場合も、**このテーマ内に該当コードが無い・`npm run init` 後でデモが無いときは、実装・提案に着手する前に** 次の型録リポジトリを読むこと。

- **型録のルート（絶対パス）**: `/Users/yoshiaki/working/t2025-10-01vite`
- 以降、`{型録}` と表記する（上記パスを結合する）。

**手順（この順で行う）**

1. 下表から **JS・マークアップ（EJS）・Sass** の該当ファイルパスを特定し、`Read` 等で内容を把握する。
2. マークアップの組み立て例が必要なら、デモページの `index.html` を読む（どの `_p-*.ejs` が載っているかの索引になる）。
3. この WordPress テーマ向けに **PHP テンプレート・既存の BEM / `ty_` 規約・[docs/01-development/coding-standards.md](docs/01-development/coding-standards.md)** に合わせて移植する。EJS のままコピーしない。**コンテンツ文言の捏造は禁止**（汎用ラベルのみ・またはプレースホルダにし、ユーザーまたは既存データに任せる）。
4. JS は型録の **`data-*` / `.js-*` クラス契約と同等の DOM 構造**を保てるならそのまま近い形で取り込み、テーマのエントリ（例: `main.js`）に import する必要があれば元テンプレの `src/assets/js/main.js` を参照する。

**主要パーツと型録ファイル（`{型録}/` 以下）**

| 機能 | JS（優先） | マークアップ（EJS） | Sass | デモページ（索引） |
|------|------------|---------------------|------|-------------------|
| タブ切り替え | `src/assets/js/demo/_tab.js` | `src/ejs/components-demo/_p-tab.ejs` | `src/assets/sass/demo-components/_p-tab.scss` | `src/demo/demo-tab/index.html` |
| アコーディオン | `src/assets/js/demo/_accordion.js` | `src/ejs/components-demo/_p-accordions.ejs` | `src/assets/sass/demo-components/_p-accordions.scss` | `src/demo/demo-accordion/index.html` |
| モーダル（`dialog` 系） | `src/assets/js/demo/_dialog-general.js`、`_dialog-youtube.js`、`_dialog-video.js`（共通: `_dialog-common.js`） | `src/ejs/components-demo/_p-dialog*.ejs`（トリガー・本体・動画用） | `_p-dialog.scss`、`_p-dialog-trigger.scss`、`_p-dialog-trigger-youtube.scss`、`_p-dialog-trigger-video.scss` | `src/demo/demo-dialog/index.html` |
| モーダル（`dialog` 以外） | `src/assets/js/demo/_modal.js` | 上記と用途に応じて `components-demo` 内を検索 | 同上または近傍 | `src/demo/demo-dialog/index.html` を参考 |
| スライダー（Splide） | `src/assets/js/demo/_splide-fade.js` ほか `_splide-loop.js`、`_splide-thumbnail.js`、`_splide-progress.js`、`_splide-posts.js` | 同名の `src/ejs/components-demo/_p-splide-*.ejs` | 同名の `src/assets/sass/demo-components/_p-splide-*.scss` | `src/demo/demo-splide/index.html` |

### アクセシビリティ仮基準（参照）

- **正本**（配置場所）: `{型録}/docs/a11y-baseline.md`
- **適用**は**静的サイトに限らない**（マークアップ・CSS・クライアントJS を扱う制作全般向けの内部ライン。WCAG 適合の宣言文書ではない）。本テーマの PHP・テンプレート・JS の**新規・修正**の際も参照してよい。

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

ナレッジリポジトリ `/Users/yoshiaki/working/2026-03-20kn/` の `coding-rules/` を参照すること。

| ファイル | 内容 |
|---|---|
| [`coding-rules/common.md`](/Users/yoshiaki/working/2026-03-20kn/coding-rules/common.md) | 実装方針・設計・判断基準 |
| [`coding-rules/php.md`](/Users/yoshiaki/working/2026-03-20kn/coding-rules/php.md) | PHP |
| [`coding-rules/ejs-html.md`](/Users/yoshiaki/working/2026-03-20kn/coding-rules/ejs-html.md) | HTML構造・クラス命名・画像実装 |
| [`coding-rules/sass.md`](/Users/yoshiaki/working/2026-03-20kn/coding-rules/sass.md) | Sass/SCSS |
| [`coding-rules/javascript.md`](/Users/yoshiaki/working/2026-03-20kn/coding-rules/javascript.md) | JavaScript |

### このテンプレート固有のルール

- **[docs/01-development/coding-standards.md](docs/01-development/coding-standards.md)**: テンプレート固有のコーディング規約（`ty_` プレフィックス・`kiso.css` 前提・プロパティ指定順など）
- **[docs/01-development/architecture.md](docs/01-development/architecture.md)**: 設計判断・MUST/SHOULD/MAY

---

## アーキテクチャ概要

詳細は [docs/01-development/architecture.md](docs/01-development/architecture.md) を参照してください。

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

- **Vite連携の仕組みを変更しない**: dev/prod判定とmanifest読み込みの仕組みは必須
- **関数名のプレフィックス**: `ty_` を必ず付与（WordPress/プラグインとの衝突回避）
- **画像パスの解決**: CSS内はViteが解決、HTML内は `ty_theme_image_url()` を使用

---

## ドキュメント化の基準

変更時に「ドキュメントを残すか」を一貫して判断するための基準です。軽微な修正はドキュメントを増やさず、再発防止・合意形成・保守に効く変更だけ記録を残します。

### ドキュメントを残さなくてよい変更（例）

- 余白・フォントサイズ・色など、単発のスタイル調整
- 既存テンプレート内の文言・ラベルの修正
- typo修正、コメントの追記・削除
- 既存ルールに沿った単純なマークアップ追加

### ドキュメントを残すべき変更（例）

- `functions.php` / `functions-lib`: 新規 `require`、フック追加・削除、enqueue の変更
- アセット読み込み: エントリ追加、Vite以外の読み込み方法の導入
- Vite設定: `vite.config.*` の変更
- ルール・規約の変更
- 依存の追加・削除・バージョン方針

### 残す場合の行き先ガイド

| 内容 | 行き先 |
|------|--------|
| なぜこの判断をしたか（技術判断・トレードオフ） | [docs/05-decisions/](docs/05-decisions/)（ADR） |
| 案件固有の仕様・制約 | [docs/04-project-specific/overview.md](docs/04-project-specific/overview.md) |
| 設計・アーキテクチャのルール | [docs/01-development/architecture.md](docs/01-development/architecture.md) |
| ハマりどころ・対処方法 | [docs/03-troubleshooting/troubleshooting.md](docs/03-troubleshooting/troubleshooting.md) |
| 移行・環境差分・履歴 | [docs/06-reference/migration-log.md](docs/06-reference/migration-log.md) |

---

## 参考ドキュメント

- **設計判断・守るルール**: [docs/01-development/architecture.md](docs/01-development/architecture.md)
- **テンプレート固有のコーディング規約**: [docs/01-development/coding-standards.md](docs/01-development/coding-standards.md)
- **開発ガイド**: [docs/01-development/development.md](docs/01-development/development.md)
