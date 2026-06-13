# WP テンプレ — 技術ドキュメント（正本）

人間向けの入口はリポジトリ直下の [README.md](../README.md)。**設計・導入・開発・デプロイ・トラブル・案件メモ・移行ログ**は本ファイルに集約する。

- **文書化の基準・Git/husky・ADR の基準**（汎用）: ナレッジベースの [template-repository-docs.md](/Users/yoshiaki/working/2026-04-23kn/wiki/template-repository-docs.md)
- **A11y 仮基準の正本**: ナレッジ `wiki/a11y-baseline.md`。**WP テンプレ固有の補足** — [コーディング規約（WP テンプレ固有）](#コーディング規約wp-テンプレ固有) の `ty_`・PHP テンプレート方針に従う。**型録（EJS）**は [AGENTS.md](../AGENTS.md) の「型録参照」（静的テンプレ `{型録}/` の `demo`・`components-demo`）。**文言の捏造は禁止**（汎用ラベル・プレースホルダに留め、表示文言は既存データ・ユーザー定義に任せる）。個別の `docs/a11y-baseline.md` は置かない。

## 目次

1. [導入・セットアップ](#導入セットアップ)
2. [導入・セットアップの詳細](#導入セットアップの詳細)
3. [設計判断・守るルール](#設計判断守るルール)
4. [コーディング規約（WP テンプレ固有）](#コーディング規約wp-テンプレ固有)
5. [開発ガイド](#開発ガイド)
6. [デプロイ](#デプロイ)
7. [トラブルシューティング](#トラブルシューティング)
8. [案件固有情報の記入先](#案件固有情報の記入先)
9. [参考・移行ログ](#参考移行ログ)

---

## 導入・セットアップ

このドキュメントでは、最小限の手順で開発環境を構築する方法を説明します。

## 前提条件

- Node.js がインストールされていること
- WordPress のローカル環境（Local など）が準備されていること

## 手順

### 1. 依存関係のインストール

```bash
npm install
```

### 2. 開発サーバーの起動

```bash
npm run dev
```

Vite dev serverが起動し、`localhost:5173` でアクセス可能になります。

### 3. WordPressでの確認

1. WordPressのテーマとして有効化
2. ブラウザでWordPressサイトにアクセス
3. 開発者ツールで、CSS/JSが `localhost:5173` から読み込まれていることを確認

## 本番ビルド

```bash
npm run build
```

`dist/` にビルド成果物が出力されます。

## 詳細なセットアップ

より詳細なセットアップ手順が必要な場合は、[導入・セットアップの詳細](#導入セットアップの詳細) を参照してください。

## トラブルシューティング

問題が発生した場合は、[トラブルシューティング](#トラブルシューティング) を参照してください。

---

## 導入・セットアップの詳細

このドキュメントでは、プロジェクトのセットアップ手順を説明します。

## 必要要件

### システム要件

- **Node.js**: 必要（バージョン要件は未確認）
- **npm**: 必要（バージョン要件は未確認）
- **メモリ**: 4GB以上（推奨: 8GB以上）

### プラットフォーム別追加要件

- **macOS**: Xcode Command Line Tools
  ```bash
  xcode-select --install
  ```
- **Windows**: Visual Studio Build Tools または Visual Studio Community
- **Linux**: build-essential
  ```bash
  sudo apt-get install build-essential
  ```

## 環境確認方法

セットアップ前に、必要なツールがインストールされているか確認してください。

### Node.js の確認

```bash
node --version
```

インストールされていることを確認してください（バージョン要件は未確認）。

### npm の確認

```bash
npm --version
```

インストールされていることを確認してください（バージョン要件は未確認）。

### プラットフォーム別確認

#### macOS

Xcode Command Line Toolsがインストールされているか確認：

```bash
xcode-select -p
```

`/Library/Developer/CommandLineTools` が表示されればOKです。

#### Windows

Visual Studio Build Toolsがインストールされているか確認：
- コントロールパネル > プログラムと機能 で確認

#### Linux

build-essentialがインストールされているか確認：

```bash
dpkg -l | grep build-essential
```

build-essential が表示されればOKです。

## プロジェクト初期セットアップ

テンプレートリポジトリから新規プロジェクトを作成する手順です。

### 1. LOCALのセットアップ

Local環境のセットアップを行います。
サイト名には案件idを使う

### 2. リポジトリ複製

GitHub CLIを使ってテンプレートリポジトリから新規リポジトリを作成・クローン：

```bash
gh repo create 新規リポジトリ名 \
  --template rea1i2e/t2025-12-24vite-wp \
  --private \
  --description "リポジトリの説明文" && \
sleep 5 && \
gh repo clone GitHubのユーザー名/新規リポジトリ名
```

- テンプレートリポジトリ名は実際のリポジトリ名に置き換えること

### 3. プロジェクトID一括置換

プロジェクト固有のIDを一括置換します。

- `t2025-12-24vite-wp` → 案件id（現在のテーマ名を新しいテーマ名に）
- **案件idの用途**：
  - テーマディレクトリ名（`wp-content/themes/案件id/`）
  - `package.json` の `name`、URL（`preview`、`test` など）
  - `style.css` の `Theme Name`
  - `tools/` 配下のパス参照
  - デプロイ先パス（`env.deploy.example` の例）
- スクリプトを使用：
  ```bash
  bash scripts/replace-theme-id.sh 案件id
  ```
- 例（案件idが `2026-01-11yn` の場合）：
  ```bash
  bash scripts/replace-theme-id.sh 2026-01-11yn
  ```
- 対象ファイル：
  - `package.json`（name、URL）
  - `package-lock.json`
  - `style.css`（Theme Name）
  - `tools/import-pages.php`（パス）
  - `tools/import-pages.sh`（パス）
  - `env.deploy.example`（`FTP_SERVER_DIR`、`TEST_URL`）
  - `docs/architecture.md`（コマンド例）
  - `docs/architecture.md`（コマンド例）
  - `docs/architecture.md`（ディレクトリ名）
  - `tools/README.md`（コマンド例）
- 注意: 以下のMDファイルはテンプレートとして残します（置換しません）
  - `README.md`
  - `docs/architecture.md`
  - `docs/architecture.md`
  - `docs/architecture.md`
- **個別に変更しても問題ない項目**：
  - `style.css` の `Theme Name`: WordPress管理画面に表示されるテーマ名なので、案件名などに変更しても問題ありません
- **テーマディレクトリ名を変更する場合の注意**：
  - テーマディレクトリ名を変更する場合は、GitHub Secrets の `FTP_SERVER_DIR` を更新してください（サーバー側のディレクトリ名と一致させる必要があります）
  - `.github/workflows/deploy.yml` には影響ありません（コメント内の例のみ）
  - `tools/` 配下のパスは一括置換で自動更新されます

### 4. Secrets指定、登録

[docs/architecture.md](../architecture.md#デプロイ) の「Secrets設定のスクリプト化」を参照

### 5. サーバー管理パネルからの作業

1. サブディレクトリ作成
2. サブFTPアカウント作成
3. Basic認証設定
4. WordPressインストール

### 6. 固定ページ登録

- `tools/pages.json` を編集して固定ページを定義
- Local の Site Shell で `wp eval-file wp-content/themes/<案件id>/tools/import-pages.php wp-content/themes/<案件id>/tools/pages.json upsert` を実行
  - `<案件id>` は「3. プロジェクトID一括置換」で設定した案件idに置き換えること
- 詳細は [tools/README.md](../tools/README.md) を参照

### 7. フォントの登録

1. デザインデータから使用フォントを確認
2. 必要なフォントファイルをダウンロード
3. `raw/fonts/README-font-compress.md` に従い、`fonttools` で woff2 化（全グリフ or サブセット）
4. 出力を `src/assets/fonts/` に配置
5. `src/assets/sass/base/_root.scss` の `@font-face` とカスタムプロパティを編集
6. `header.php` の `preload` を必要に応じて追加（`ty_vite_asset_url('src/assets/fonts/...')`）

### 8. カスタムプロパティ・Sass変数の登録

1. `src/assets/sass/global/_setting.scss` でSass変数を定義
   - `$inner-sp`、`$inner-pc`（コンテンツ幅）
   - `$padding-sp`、`$padding-pc`（パディング）
   - その他のレイアウト値
2. `src/assets/sass/base/_root.scss` の `:root` 内でカスタムプロパティを定義
   - 色（`--color-theme`、`--color-accent` など）
   - サイズ（`--header-height` など）
   - その他のデザイン値

## 開発環境セットアップ

リポジトリがクローン済みの状態で開発環境を構築する手順です。

### 1. Node.js のインストール

Node.jsをインストールしてください（バージョン要件は未確認）。

- [Node.js公式サイト](https://nodejs.org/) からダウンロード
- または、[nvm](https://github.com/nvm-sh/nvm) を使用してインストール

### 2. 依存関係のインストール

```bash
npm install
```

### 3. 開発サーバーの起動

```bash
npm run dev
```

Vite dev serverが起動し、`localhost:5173` でアクセス可能になります。

### 4. WordPressでの確認

1. WordPressのテーマとして有効化
2. ブラウザでWordPressサイトにアクセス
3. 開発者ツールで、CSS/JSが `localhost:5173` から読み込まれていることを確認

## トラブルシューティング

セットアップ時に問題が発生した場合は、[docs/architecture.md](../architecture.md#トラブルシューティング) を参照してください。

よくある問題：

- `sharp` のビルド/インストール失敗
  - macOS: Xcode Command Line Tools の導入を確認
  - Node のメジャー更新後は `npm rebuild sharp` を試す
- 権限エラー
  - `dist/` やプロジェクトルートの書き込み権限を確認

---

## 設計判断・守るルール

このドキュメントは、プロジェクトの設計判断と守るべきルールを、MUST/SHOULD/MAYの3段階で分類しています。

## MUST（変えてはいけないルール）

絶対に守るべきルールです。これらを変更すると、プロジェクトの基盤が崩れる可能性があります。

### Vite連携の仕組み

- **dev/prod判定**: `functions-lib/func-vite.php` の `ty_vite_is_dev()` で判定する仕組みを変更してはいけません
- **manifest読み込み**: 本番環境では `dist/.vite/manifest.json` を読み込んでアセットを解決する仕組みを変更してはいけません
- **エントリポイント**: `src/assets/js/main.js` と `src/assets/sass/style.scss` をエントリとして使用する仕組みを変更してはいけません

### 関数名のプレフィックス

- **`ty_` プレフィックス**: テーマ内で定義するPHP関数には必ず `ty_` プレフィックスを付与してください
  - 理由: WordPressやプラグインの関数名との衝突を避けるため
  - 例: `ty_theme_image_url()`, `ty_register_post_type_works()`

### WordPressテーマの基本構造

- **テーマヘッダ**: `style.css` にテーマ情報を記載する必要があります
- **テンプレート階層**: WordPressのテンプレート階層に従ってファイルを配置してください

## SHOULD（推奨ルール）

推奨されるが、状況に応じて変更可能なルールです。

### ディレクトリ構成

- **`functions-lib/`**: PHP機能ファイルを分割して配置することを推奨します
- **`components/`**: 再利用可能なPHPコンポーネントを配置することを推奨します
- **`src/assets/`**: 開発用ソース（Sass/JS/画像/フォント）を配置することを推奨します
- **`dist/`**: ビルド成果物を出力するディレクトリとして使用することを推奨します

### コーディングスタイル

詳細は下記「[コーディング規約（WP テンプレ固有）](#コーディング規約wp-テンプレ固有)」を参照。

### 画像の扱い

- **CSS内の画像**: `src/assets/images/**` を Sass の `url(...)` 経由で参照することを推奨します
- **HTML内の画像**: `ty_theme_image_url()` を使用して画像URLを取得することを推奨します
- **WordPressメディア**: WordPressのメディアライブラリを使用する場合は `wp_get_attachment_image()` を優先することを推奨します

## MAY（状況次第で変えていい判断）

案件ごとに判断して良い項目です。

### 画像最適化

- **設定の正本**: [`config/theme-build.config.js`](config/theme-build.config.js)（`imageAltFormats`・`useFileHash`・`cssMinify`・品質など）。変更後は `npm run build`
- **代替フォーマット**: `imageAltFormats` — `none` | `webp` | `avif` | `both`（WP テンプレ既定は `none` — WebP/AVIF はプラグイン想定）
- **画像圧縮**: `@vheemstra/vite-plugin-imagemin`（品質は `theme-build.config.js` の `jpegQuality` / `webpQuality` 等）

### カスタム投稿タイプ

- **投稿タイプの追加**: 案件に応じてカスタム投稿タイプを追加可能です
- **タクソノミーの追加**: 案件に応じてタクソノミーを追加可能です

### コンポーネント構成

- **コンポーネントの追加**: 案件に応じて `components/` 配下にコンポーネントを追加可能です
- **コンポーネントの構造**: 案件に応じてコンポーネントの構造を変更可能です

### デプロイ設定

- **デプロイ先**: GitHub Actionsのワークフローを変更して、デプロイ先を変更可能です
- **通知設定**: Discord通知の有無は案件に応じて設定可能です

### 開発ツール

- **Husky**: 必要に応じて追加可能です（現状は使用していません）
- **Linter/Formatter**: 案件に応じて追加可能です

### 管理画面メニュー

- **非表示・トップレベル表示**: `functions-lib/func-admin-menu.php` で配列（`$ty_admin_menu_remove_slugs`, `$ty_admin_page_menu_slugs`）により制御します。
- **`edit.php` を非表示にする場合**: 「投稿」メニューが消えるため、`func-set-posttype-post.php` のラベル変更（お知らせ）は見た目に効かなくなります。お知らせとして残す場合は非表示リストに `edit.php` を入れないこと。
- 意思決定の背景はナレッジ [wp-template-decision-records.md（§6）](/Users/yoshiaki/working/2026-04-23kn/wiki/wp-template-decision-records.md#6-管理画面メニューのカスタマイズ) を参照。

### 標準投稿（post）— カテゴリー・タグ UI の非表示（任意）

標準の「投稿」で**カテゴリー・タグを運用しない**案件向け。実装は [`functions-lib/func-set-posttype-post.php`](functions-lib/func-set-posttype-post.php)。**既定は無効**（フィルター false）。

**案件設定（`functions-lib/func-set-posttype-post.php` 冒頭の定数）:**

```php
const TY_POST_LABEL_NAME = 'お知らせ';
const TY_POST_HIDE_CATEGORY_UI = false; // 使わない案件は true
const TY_POST_HIDE_TAG_UI = false;      // 使わない案件は true
```

`functions.php` はローダー専用。上記定数を編集するか、フィルター `ty_post_label_name` / `ty_post_hide_category_ui` / `ty_post_hide_tag_ui` で上書きする。カテゴリーとタグは個別に切り替え可能。

**非表示範囲（true のとき）:** 投稿一覧のカテゴリー・タグ列、左メニュー配下のカテゴリー・タグ、編集画面メタボックス、クイック編集のタクソノミー欄。`register_taxonomy` で管理 UI を off（`unregister_taxonomy` はしない — 未分類付与はコア任せ）。

ラベル変更（`ty_post_label_name` 等）は本フィルターと**独立**して常時動作する。

## 詳細情報

### ファイル構成詳細

テンプレートファイル・投稿タイプ・機能ファイルの一覧は [案件固有情報の記入先](#案件固有情報の記入先) を参照してください。

### リクエスト〜テンプレートの時系列

WordPress コア一般の整理（レイヤー図・`functions.php` ローダー・`get_header` 周りの Mermaid）は、ナレッジベースの [`wordpress-request-and-theme-lifecycle.md`](/Users/yoshiaki/working/2026-04-23kn/wiki/wordpress-request-and-theme-lifecycle.md) を参照してください。

**WP テンプレ内**で押さえるファイル（実装の正本）は次のとおりです。

| 対象 | 内容 |
|------|------|
| `functions.php` のローダー | テーマ直下 `functions.php` — `$ordered` で `functions-lib/func-vite.php` を先に `require_once`、残りは `functions-lib/*.php` をファイル名昇順（`_` 始まり等は除外）。 |
| アセットの enqueue | `functions-lib/func-vite-assets.php` の `add_action( 'wp_enqueue_scripts', 'ty_enqueue_assets' )`。コールバックの**実行**はフロントでは `header.php` から呼ばれる `wp_head()` 内（`do_action( 'wp_enqueue_scripts' )`）で行われる。 |
| トップ表示の流れ（例） | `front-page.php` がエントリ → `get_header()` / `header.php` → `wp_head()` → メイン（`get_template_part` の呼び出し順が実行順）→ `get_footer()` / `footer.php` → `wp_footer()`。フォールバックは `index.php` など。 |
| Vite 解決・dev 判定 | `functions-lib/func-vite.php`（`ty_vite_is_dev()` 等）。 |

直後の「全体フロー（開発・本番）」は、上記の enqueue 以降の **Vite dev server / `dist` と manifest** の話として読むと、位置づけが揃います。

### 全体フロー（開発・本番）

#### 開発環境（dev）のフロー

```
ブラウザ → WordPressページリクエスト
    ↓
WP_Theme_PHP（header.php, functions.php など）
    ↓
load_assets（アセット読み込み指示）
    ↓
Vite_dev_server(HMR) ← localhost:5173 から CSS/JS を取得
```

#### 本番環境（prod）のフロー

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

ポイント:
- WordPressはPHPテンプレートでHTMLを生成するため、**HTMLをエントリにしない**。
- 代わりに、Viteのエントリは **JS/CSSファイル**（`src/assets/js/main.js`, `src/assets/sass/style.scss`）。

### Vite連携の詳細

#### dev / prod の判定方法

判定は `functions-lib/func-vite.php` の `ty_vite_is_dev()` で行います。

- `functions-lib/func-vite.php` で `TY_VITE_DEV_SERVER` を定義（`is_ssl()` に応じて `https://localhost:5173` または `http://localhost:5173`）
- `TY_VITE_DEV_SERVER/@vite/client` に `wp_remote_head()` で到達確認（短いtimeout）
  - 到達できる: **dev扱い**（Vite dev serverから `@vite/client` と `src/assets/**` を読み込む）
  - 到達できない: **prod扱い**（`dist/.vite/manifest.json` を参照して `dist/assets/**` をenqueue）

同一マシン上で **別プロセスの Vite が同じ `TY_VITE_DEV_SERVER` で応答している場合も dev 扱い**になる。運用では **5173 をこのテーマ用に確保**し、衝突時は占有側を止める（詳細はナレッジ [wp-template-decision-records.md（§9）](/Users/yoshiaki/working/2026-04-23kn/wiki/wp-template-decision-records.md#9-vite-開発時のポート-5173-運用)、手順は [開発ガイド](#開発ガイド) の「Vite dev server（ポート5173）の運用」と [トラブルシューティング](#トラブルシューティング)）。

### 画像・フォントの扱い

- 画像（背景画像等）: `src/assets/images/**` を Sass の `url(...)` 経由で参照し、Viteがビルド対象として解決
- フォント: `src/assets/fonts/**` を `@font-face` で参照し、ビルドで `dist/assets/*.woff2` に出力
- フォントの WOFF2 変換（作業用）: **`raw/fonts/`** に静的テンプレと同じ `font-compress.sh` / `font-compress-subset.sh` / `README-font-compress.md` を置く。`raw/` はデプロイ除外

補足（`<img>` の画像）:
- `<img>` はCSSの `url(...)` のように参照を辿れないため、`vite build` 時に `src/assets/images/**` を `dist/assets/images/**` へ出力し、`dist/theme-assets.json` を生成してPHPが解決する方式を採用
- **画像の width/height**: ビルド時に Vite の `wpThemeImagesManifest` プラグイン（vite.config.js）が `image-size`（ラスター画像）または SVG の viewBox 等パースで寸法を取得し、`dist/theme-assets.json` に保存する。表示時は `ty_theme_image_dimensions()`（func-vite.php）がその JSON を参照し、エントリが無い場合（未ビルド・開発時など）のみ `getimagesize()` にフォールバックする
- **WebP/AVIF（任意）**: ビルドで `dist/assets/images/` に兄弟ファイルとして出力可能。PHP は `dist/theme-build-config.json` の `imageAltFormats` を読み、`ty_img` 等で format 用 `<source>` を付与。dev（Vite 到達時）は format 化しない。WP テンプレ既定は `none`

---

## コーディング規約（WP テンプレ固有）

汎用的なコーディングルール（HTML/CSS/Sass/JS/PHP 共通）は、ナレッジベースの `/Users/yoshiaki/working/2026-04-23kn/wiki/coding-conventions.md` および子ページ（`wiki/coding-*.md`）を参照してください。旧 `2026-03-20kn` の `coding-rules/` は廃止とする。

このドキュメントには、**WP テンプレ固有のルール**のみを記載します。

---

## PHP（WP テンプレ固有）

### 関数名プレフィックス

- **テーマ内で定義するPHP関数には必ず `ty_` プレフィックスを付与する** — WordPressやプラグインの関数名との衝突を避けるため。
  - 例: `ty_theme_image_url()`, `ty_register_post_type_works()`

### `function_exists` の使用ルール

- **プラグインで定義されている関数を使うときのみ `function_exists` を使用する**
- **子テーマを想定しないため、重複防止のための `function_exists` は不要**
- **同一テーマ内の関数を参照する場合も `function_exists` チェックは不要**（ファイル読み込み順序で解決）

### ファイル読み込み順序

- **`functions.php` の `$ordered`**: 依存関係があるファイルのみ追加する（通常は空でOK）
  - トップレベルで別ファイルの関数/定数を参照する場合は順序指定が必要

### コーディングスタイル

- **型宣言を付与する**（`declare(strict_types=1);` を使用）

### `components/` のマークアップとデータ

固定ページセクション等を `components/*.php` に切り出すときの**中身の書き方**（固定コピーは HTML 直書き、繰り返しだけ配列、配列は `foreach` 直前、エスケープの住み分け）は、ナレッジ [`wiki/coding-php.md`](/Users/yoshiaki/working/2026-04-23kn/wiki/coding-php.md) の **「データ取得とテンプレート」** を正本とする。コンポーネント化そのもののトレードオフは同 wiki の [wordpress-php-templates-component-vs-inline-tradeoff.md](/Users/yoshiaki/working/2026-04-23kn/wiki/wordpress-php-templates-component-vs-inline-tradeoff.md)。

---

## CSS/Sass（WP テンプレ固有）

### `kiso.css` を前提とした記述

以下は `kiso.css`（npm）・`_reset.scss`・`_base.scss` で適用済みのため、個別SCSSで重複して書かない。

#### kiso.css で適用済み

| プロパティ | 対象 |
|---|---|
| `box-sizing: border-box` | `*, ::before, ::after` |
| `margin: unset` | `body` |
| `margin-block: unset` | `h2〜h6`, `p`, `blockquote`, `figure`, `pre`, `ul`, `ol`, `dl`, `menu` |
| `padding-inline-start: unset` | `ul`, `ol`, `menu` |
| `list-style-type: ""` | `ul`, `ol`, `menu` |
| `color: unset` / `text-decoration-line: unset` | `a:any-link` |
| `vertical-align: bottom` / `max-inline-size: 100%` / `block-size: auto` | `img`, `video`, `iframe` など埋め込み要素 |
| `border-collapse: collapse` | `table` |
| `border: unset` / `font: unset` / `color: unset` など | `button`, `input`, `select`, `textarea` |
| `cursor: pointer` | クリッカブル要素 |
| `line-height: 1.5` | `:root` |
| `font-family: sans-serif` | `:root` |
| `overflow-wrap: anywhere` | `:root` |
| `background-color: unset` | `button`, `input[type="button"]` など |
| `touch-action: manipulation` | `button`, `[role="button"]` など |

#### `_reset.scss` で追加適用済み

| プロパティ | 対象 |
|---|---|
| `display: block; width: 100%` | `img` |
| `display: block; max-width: 100%; height: auto` | `iframe` |
| `border-width: 0; padding: 0` | `button` |
| `margin-block: unset` | `h1` |

#### `_base.scss` で適用済み

| プロパティ | 対象 |
|---|---|
| `container-type: inline-size`, `display: grid`, `min-height: 100vh`, `line-height: 1.5`, `font-family: var(--base-font-family)` | `body` |
| `transition: opacity var(--hover-transition)` | `a` |
| `opacity: 0.7` on hover / focus-visible | `a` |
| `pointer-events: none`（PC時） | `a[href^="tel:"]` |
| `aspect-ratio: 16 / 9` | `iframe[src*="youtube.com"]` |

### プロパティの指定順

CSSプロパティの指定順は、メディアクエリの有無を優先し、その中でカテゴリ別の順序で記述する：

1. メディアクエリなしのスタイル（基本スタイル）を先に記述
2. メディアクエリありのスタイル（`@include mq()`）を後に記述
3. カテゴリ別の順序:
   1. レイアウト関連: `display`、`position`、`z-index`、`flex-direction`、`align-items`、`gap`、`margin` など
   2. サイズ・スペーシング: `width`、`height`、`min-height`、`max-width`、`padding` など
   3. 見た目（背景・ボーダー）: `background-color`、`border`、`border-radius`、`box-shadow`、`filter` など
   4. テキスト・フォント: `font-size`、`font-weight`、`color`、`text-align`、`line-height` など
   5. その他: `cursor`、`transition`、`opacity` など

---

## 画像の扱い（WP テンプレ固有）

### CSS内の画像

- `src/assets/images/**` を Sass の `url(...)` 経由で参照する（Viteがビルド時に解決）

### HTML内の画像

- `ty_theme_image_url()` を使用して画像URLを取得する
- WordPressのメディアライブラリを使用する場合は `wp_get_attachment_image()` を優先する

---

## 開発ガイド

このドキュメントでは、開発時の作業フローやスクリプトの使い方を説明します。

## 開発フロー

1. `npm run dev` を実行してVite dev serverを起動
2. WordPressでテーマを有効化
3. `src/` 以下を編集（Sass/JS/画像/フォント）
4. ブラウザで自動反映を確認（HMR対応）

### Vite dev server（ポート5173）の運用

dev / prod の切り替えは `ty_vite_is_dev()` が **`TY_VITE_DEV_SERVER` の `/@vite/client` に到達できるか**で判定する（既定は `localhost:5173`）。**別プロジェクトの Vite が同じポートで動いていると、こちらも dev 扱いになる**ため、このテーマで開発するときは **5173 をこのテーマ用の Vite に専有**させる。

- 他案件で 5173 を使っている場合は、**必要に応じて先にそちらを止める**（または該当の dev サーバーを終了する）。
- ポートがどのプロセスか確認する例（macOS）: `lsof -nP -iTCP:5173 -sTCP:LISTEN`
- 方針の記録: ナレッジ [wp-template-decision-records.md（§9）](/Users/yoshiaki/working/2026-04-23kn/wiki/wp-template-decision-records.md#9-vite-開発時のポート-5173-運用)

## スクリプト

### 開発サーバー

Sass/JSを監視して自動反映（HMR対応）：

```bash
npm run dev
```

### 本番ビルド

`dist/` に出力 + ビルド時最適化：

```bash
npm run build
```

ビルド時の処理：
- WebP/AVIF 生成（`config/theme-build.config.js` の `imageAltFormats`、WP テンプレ既定 `none`）
- 画像圧縮（JPEG/PNG/GIF/SVG）
- CSS 背景の `image-set` 付与（`postbuild-image-set.mjs`）
- JS/CSS/フォント/画像のファイル名ハッシュ（`useFileHash: true` が既定）

### ビルド確認（簡易サーバー）

ビルド後のファイルをブラウザで確認：

```bash
npm run preview
# もしくは
npm run build:preview
```

### クリーンアップ

不要なファイルを削除：

```bash
npm run clean        # dist と一時生成を削除
npm run clean:all    # + node_modules と lock も削除
npm run reinstall    # クリーン後に再インストール
```

## ディレクトリ構成

```
src/                 # 開発用ルート（Vite root）
  assets/
    sass/            # Sass（グロブインポート対応）
      base/          # リセット、ベーススタイル
      components/    # コンポーネントスタイル
      layout/        # レイアウトスタイル
      utility/       # ユーティリティクラス
      style.scss     # エントリファイル
    js/              # JSモジュール
      main.js        # エントリファイル
      _*.js          # 機能別モジュール
    images/          # 画像（common/demo等）
    fonts/           # フォント
dist/                # 本番出力（ビルド生成物）
  assets/
    css/             # コンパイル済みCSS
    js/              # バンドル済みJS
    images/          # 最適化済み画像
  .vite/
    manifest.json    # パスマッピング
  theme-assets.json  # 画像パスマッピング
  theme-build-config.json  # imageAltFormats 等（PHP 参照）
```

## Sass/スタイル

### グロブインポート

`vite-plugin-sass-glob-import` により、Sass のグロブインポートが可能です。

```scss
// style.scss
@import "base/**/*.scss";
@import "components/**/*.scss";
@import "layout/**/*.scss";
```

### ディレクトリ構成

- `base/`: リセット、ベーススタイル、変数定義
- `components/`: コンポーネントスタイル
- `layout/`: レイアウトスタイル
- `utility/`: ユーティリティクラス

### 画像参照

Sass内で画像を参照する場合：

```scss
background-image: url("../images/common/logo.svg");
```

Viteがビルド時に解決し、ハッシュ付きファイル名に変換されます。

## JavaScript

### モジュール構成

`src/assets/js/main.js` がエントリファイルです。機能別にモジュールを分割して、`main.js` でインポートします。

```javascript
// main.js
import './_header.js';
import './_drawer.js';
import './_modal.js';
```

### モジュール命名規則

機能別モジュールは `_` プレフィックスを付与：

- `_header.js`: ヘッダー関連
- `_drawer.js`: ドロワーメニュー
- `_modal.js`: モーダル
- `_accordion.js`: アコーディオン

## 画像最適化（ビルド時）

### プラグイン

`@vheemstra/vite-plugin-imagemin` を使用して画像を最適化します。

### 圧縮対象

- PNG/JPEG/GIF/SVG

### WebP / AVIF 生成（静的テンプレ `imageAltFormats` 相当）

正本: [`config/theme-build.config.js`](config/theme-build.config.js)。値を変えたら `npm run build`。ビルド時に `dist/theme-build-config.json` へ書き出し、PHP（prod）が参照します。

| `imageAltFormats` | ビルド | PHP（prod） |
|----|--------|-------------|
| `none` | 代替フォーマットなし（WP テンプレ既定） | `<img>` のみ |
| `webp` | WebP のみ | `<source type="image/webp">` |
| `avif` | AVIF のみ | `<source type="image/avif">` |
| `both` | WebP + AVIF | AVIF → WebP の順 |

`theme-build.config.js` の主な項目:
- `imageAltFormats` — 上記
- `useFileHash` — WP テンプレ既定 `true`（`false` でハッシュなし出力）
- `cssMinify` — CSS を minify するか（既定 `true`）
- `jpegQuality` / `webpQuality` — 圧縮品質（既定 `75`）
- `skipIfLargerThan` — 元より大きい代替は出力しない（既定 `true`）

### 出力パス

`useFileHash: true`（既定）のとき:
- 画像: `assets/images/[name]-[hash][ext]`
- CSS: `assets/css/[name]-[hash].css`
- JS: `assets/js/[name]-[hash].js`

`useFileHash: false` のときは `[hash]` なし（案件例: ik）

## PHP開発

### テンプレートファイル

WordPressのテンプレート階層に従ってファイルを配置します。

- `front-page.php`: トップページ
- `page.php`: 固定ページ
- `single.php`: 個別投稿
- `archive.php`: アーカイブ
- `404.php`: 404エラーページ

### コンポーネント

再利用可能なPHPコンポーネントは `components/` 配下に配置します。マークアップ部品（`get_template_part` で読む `.php`）のデータと HTML の分離ルールは [PHP（WP テンプレ固有）— `components/` のマークアップとデータ](#components-のマークアップとデータ) を参照。

```php
<?php
// components/c-button.php — 関数を定義する部品の例
function ty_button($text, $url, $attrs = []) {
  // ...
}
?>
```

### 機能ファイル

機能別に分割したPHPファイルは `functions-lib/` 配下に配置します。

- `func-vite.php`: Vite連携
- `func-images.php`: 画像関連
- `func-nav-items.php`: ナビゲーション

詳細は [設計判断・守るルール](#設計判断守るルール) を参照してください。

## PHP変更の即時反映（dev時）

PHPはHMRで差し替えできないため、**PHPファイル変更を検知してブラウザをフルリロード**します。

`vite.config.js` にPHP変更で `full-reload` を投げる処理が追加済みです。

## HTTPS環境での開発

WordPress表示URLがHTTPSの場合、Vite dev serverもHTTPSで配信しないとMixed Contentでブロックされます。

### 証明書の準備

1. テーマ直下に `.certs/` ディレクトリを作成
2. 以下を配置：
   - `localhost.pem`
   - `localhost-key.pem`

### 開発サーバーの起動

```bash
npm run dev
```

HTTPS設定が有効な場合、`https://localhost:5173` でアクセス可能になります。

## 固定ページ一括インポート

固定ページを一括で作成/更新するツールが用意されています。

- **ツール**: `tools/import-pages.php`
- **設定ファイル**: `tools/pages.json`
- **詳細**: [tools/README.md](../tools/README.md) を参照

### 使い方

1. `tools/pages.json` を編集して固定ページの定義を記述
2. Local の Site Shell で実行：
   ```bash
   # dry-run（確認）
   wp eval-file wp-content/themes/t2025-12-24vite-wp/tools/import-pages.php wp-content/themes/t2025-12-24vite-wp/tools/pages.json upsert 1
   
   # 本番実行
   wp eval-file wp-content/themes/t2025-12-24vite-wp/tools/import-pages.php wp-content/themes/t2025-12-24vite-wp/tools/pages.json upsert
   ```

### 運用方針

- **Local でページを作成・更新**します
- **テスト環境 / 本番環境へは、このツールで直接投入せず**、WordPressの「エクスポート / インポート（XML）」で移行します

詳細は [tools/README.md](../tools/README.md) を参照してください。

## トラブルシューティング

開発時に問題が発生した場合は、[トラブルシューティング](#トラブルシューティング) を参照してください。

---

## デプロイ

このドキュメントでは、GitHub Actionsを使った自動デプロイの設定方法を説明します。

## 概要

GitHub ActionsでFTP経由の自動デプロイを実行します。

- **トリガー**: `main`/`master` ブランチへのpush、PR作成
- **デプロイ先**: FTPサーバー（**テーマ**用と**ドキュメントルート Basic**用の2ステップ）
- **デプロイ対象**: ビルド成果物（`dist/`、`*.php`、`style.css` など）

## 必要なGitHub Secrets

以下のシークレットをGitHubリポジトリに設定する必要があります。

### 必須

- `FTP_SERVER`: FTPサーバーのアドレス
- `FTP_USERNAME`: FTPユーザー名
- `FTP_PASSWORD`: FTPパスワード
- `FTP_SERVER_DIR`: サーバー上のデプロイ先ディレクトリ
  - 例: `/public_html/wp-content/themes/t2025-12-24vite-wp/`
  - 「テーマディレクトリ直下」にこのリポジトリの中身（`style.css`, `*.php`, `functions-lib/`, `components/`, `dist/` など）を配置する想定
- `FTP_SERVER_DIR_DOCROOT`: **サイトのドキュメントルート**（`index.php` がある階層）向けの FTP パス。`staging/docroot/` に生成した **`.htaccess`（HTTP Basic + WordPress 既定リライト）** と **`.htpasswd`** だけを送るジョブで使用
  - サブ FTP のチルートがそのサイト直下なら、多くは `/` でよい（ホストのパス表記に合わせて調整）
- **既存リポジトリ:** `.env.deploy` に `FTP_SERVER_DIR_DOCROOT=/`（適宜ホストに合わせる）を**追記**し、`./scripts/setup-secrets.sh` を再実行して GitHub Secret を登録する

### 任意

- `DISCORD_WEBHOOK`: Discord通知を使う場合のWebhook URL
- `TEST_URL`: デプロイ通知/サマリーに表示するURL

### 注意（ドキュメントルートの `.htaccess`）

- CI が生成する `.htaccess` は **上段: Basic**、**下段: WordPress 標準のリライト**です。**既にサーバーに手修正したルール**がある場合、デプロイで **上書きされると失う**ので、初回は **差分を退避**するか、**「設定 → パーマリンク」で保存し直し**て WP にブロックを再生成させる運用を検討してください。
- **サブディレクトリに WordPress を置いた構成**では `RewriteBase` / 最終 `RewriteRule` を **手直し**する必要があります。

## Secrets設定のスクリプト化（gh）

GitHub CLI（`gh`）を使って、Secretsをコマンドで投入できます。

### 1. 雛形をコピーして編集

```bash
cp env.deploy.example .env.deploy
```

`.env.deploy` ファイルはコミットしないでください（`.gitignore` に追加済み）。

### 2. .env.deploy に値を記入

```bash
# .env.deploy
FTP_SERVER=ftp.example.com
FTP_USERNAME=username
FTP_PASSWORD=password
FTP_SERVER_DIR=/public_html/wp-content/themes/t2025-12-24vite-wp/
FTP_SERVER_DIR_DOCROOT=/
DISCORD_WEBHOOK=https://discord.com/api/webhooks/...
TEST_URL=https://example.com
```

### 3. GitHub CLIでログイン（初回のみ）

```bash
gh auth login
```

### 4. Secretsを投入

```bash
bash scripts/setup-secrets.sh
```

または、実行権限を付けて実行：

```bash
chmod +x scripts/setup-secrets.sh
./scripts/setup-secrets.sh
```

## デプロイワークフロー

### ワークフローファイル

`.github/workflows/deploy.yml` でデプロイワークフローを定義しています。

### デプロイの流れ

1. **コードチェックアウト**: リポジトリのコードを取得
2. **Node.js環境セットアップ**: Node.js 20をセットアップし、npmキャッシュを有効化
3. **依存関係のインストール**: `npm ci` で依存関係をインストール
4. **ビルド**: `npm run build` でViteビルドを実行（`dist/` に成果物を出力）
5. **docroot Basic の可否**: リポジトリ名（`github.event.repository.name`）が案件 ID 形式（正規表現 `^[0-9]{4}-[0-9]{2}-[0-9]{2}[a-zA-Z]{2}$`、例 `2026-05-08ex`）のときのみ docroot 系ステップへ進む。**テンプレート名**（例 `t2025-12-24vite-wp`）では docroot はスキップし、テーマ FTP のみで完走する
6. **apache2-utils**: `htpasswd` 用にパッケージをインストール（Ubuntu ランナー・**上記がマッチするときのみ**）
7. **ドキュメントルート用 Basic 生成**: `scripts/build-staging-docroot-basic.sh` が `CASE_ID=${{ github.event.repository.name }}` で `staging/docroot/.htaccess` と `.htpasswd` を生成（サイト全体 Basic + WP 既定リライト・**マッチ時のみ**）
8. **デプロイサマリー生成**: GitHub Actionsのサマリーにデプロイ情報を出力
9. **FTPデプロイ（テーマ）**: `SamKirkland/FTP-Deploy-Action` で `FTP_SERVER_DIR` へアップロード（従来どおり・常に実行）
10. **FTPデプロイ（ドキュメントルート Basic）**: 同 Action で `staging/docroot/` を `FTP_SERVER_DIR_DOCROOT` へ（**`dangerous-*` は使わず**、ルートの他ファイルは削除しない・**マッチ時のみ**）
11. **Discord通知**: デプロイ成功/失敗時にDiscordに通知（オプション）

### デプロイ対象の環境

#### テスト環境（自動デプロイ）

- **トリガー**: `main`/`master` ブランチへのpush
- **条件**: 常に自動実行
- **通知**: 成功時にDiscord通知（`DISCORD_WEBHOOK` が設定されている場合）

#### PRテスト環境（自動デプロイ）

- **トリガー**: `main`/`master` へのPR作成
- **条件**: PRが作成された場合に自動実行
- **通知**: 成功時にDiscord通知（`DISCORD_WEBHOOK` が設定されている場合）

## デプロイ除外ファイル

以下のファイル/ディレクトリはデプロイ対象外です：

- `.git*`, `.github/`
- `node_modules/`, `src/`, `scripts/`, `raw/`, `docs/`
- `package.json`, `package-lock.json`
- `vite.config.*`, `postcss.config.*`
- `*.map`, `README.md`
- `.DS_Store`, `Thumbs.db`

**方針**: サーバには **開発用ファイルを置かない**想定です。

## デプロイの確認

### GitHub Actionsのログ

1. GitHubリポジトリの「Actions」タブを開く
2. 最新のワークフロー実行を確認
3. ログでデプロイの成功/失敗を確認

### デプロイサマリー

ワークフロー実行後、サマリーに以下の情報が表示されます：

- デプロイ日時
- デプロイ先URL（`TEST_URL` が設定されている場合）
- デプロイされたファイル数

### サーバーでの確認

1. FTPサーバーに接続
2. `FTP_SERVER_DIR` で指定したディレクトリを確認
3. `dist/` 配下にビルド成果物が配置されていることを確認

## トラブルシューティング

### デプロイが失敗する

- **FTP接続エラー**: `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD` を確認
- **パスエラー**: `FTP_SERVER_DIR` が正しいか確認
- **権限エラー**: FTPユーザーに書き込み権限があるか確認

### ビルドが失敗する

- **Node.jsバージョン**: Node.js 18.x以上が必要
- **依存関係**: `npm ci` で依存関係が正しくインストールされているか確認

### Discord通知が届かない

- **Webhook URL**: `DISCORD_WEBHOOK` が正しく設定されているか確認
- **Webhook有効性**: DiscordのWebhookが有効になっているか確認

詳細は [トラブルシューティング](#トラブルシューティング) を参照してください。

---

## トラブルシューティング

このドキュメントでは、よくあるトラブルとその解決方法を説明します。

## セットアップ関連

### `sharp` のビルド/インストール失敗

**症状**: `npm install` 時に `sharp` のビルドが失敗する

**原因**: ネイティブモジュールのビルドに必要なツールが不足している

**解決方法**:

- **macOS**: Xcode Command Line Tools の導入を確認
  ```bash
  xcode-select --install
  ```
- **Node のメジャー更新後**: `npm rebuild sharp` を試す
  ```bash
  npm rebuild sharp
  ```

### 権限エラー

**症状**: `dist/` やプロジェクトルートへの書き込みができない

**原因**: ファイルシステムの権限が不足している

**解決方法**:

- `dist/` やプロジェクトルートの書き込み権限を確認
- 必要に応じて権限を変更：
  ```bash
  chmod -R 755 dist/
  ```

## 開発環境関連

### Vite dev serverに接続できない

**症状**: WordPressページでCSS/JSが読み込まれない

**原因**: Vite dev serverが起動していない、または接続できない

**解決方法**:

1. `npm run dev` が実行されているか確認
2. `localhost:5173` に直接アクセスして確認
3. ファイアウォール設定を確認

### ポート5173が占有されている、または別プロジェクトのViteが動いている

**症状**: `npm run dev` が **5173 で待受できない**（`EADDRINUSE` など）。または WordPress 上で **dev 用の読み込みになるが CSS/JS が期待と違う**・HMR がおかしい。

**原因**: 同一マシンで **別の Vite（または別プロジェクト）がすでに 5173 を使用**している。`ty_vite_is_dev()` は「その URL の `/@vite/client` に応答があるか」だけを見るため、**別案件の dev サーバーでも dev 判定**になりうる。

**解決方法**:

1. 待受プロセスを確認する（macOS の例）:
   ```bash
   lsof -nP -iTCP:5173 -sTCP:LISTEN
   ```
2. **PID とコマンド名を確認**し、Vite / Node の該当プロセスであることを確かめてから終了する（不要なら該当ターミナルで `Ctrl+C`）。**内容が分からないプロセスを無闇に kill しない**こと。
3. このテーマで開発する場合は、**5173 はこのリポジトリの `npm run dev` に空ける**運用とする（方針はナレッジ [wp-template-decision-records.md（§9）](/Users/yoshiaki/working/2026-04-23kn/wiki/wp-template-decision-records.md#9-vite-開発時のポート-5173-運用)）。
4. 日々の手順は [開発ガイド](#開発ガイド) の「Vite dev server（ポート5173）の運用」を参照。

### HTTPS環境でMixed Contentエラー

**症状**: HTTPSのWordPressページでCSS/JSが読み込まれない（Mixed Contentエラー）

**原因**: HTTPのVite dev serverにHTTPSページからアクセスしようとしている

**解決方法**:

1. `.certs/` ディレクトリに証明書を配置
2. `vite.config.js` でHTTPS設定が有効になっているか確認
3. Vite dev serverが `https://localhost:5173` で起動しているか確認

### HMR（Hot Module Replacement）が動作しない

**症状**: ファイルを変更してもブラウザが自動更新されない

**原因**: WebSocket接続が確立できていない

**解決方法**:

1. ブラウザのコンソールでエラーを確認
2. ファイアウォール/プロキシ設定を確認
3. Vite dev serverを再起動

### PHP変更が反映されない

**症状**: PHPファイルを変更してもブラウザが更新されない

**原因**: PHPはHMRで差し替えできないため、フルリロードが必要

**解決方法**:

- `vite.config.js` にPHP変更検知の設定が追加されているか確認
- 手動でブラウザをリロード

## ビルド関連

### ビルドが失敗する

**症状**: `npm run build` がエラーで終了する

**原因**: 様々な原因が考えられます

**解決方法**:

1. **Node.jsバージョン**: Node.js 18.x以上であることを確認
   ```bash
   node --version
   ```

2. **依存関係**: `npm ci` で依存関係を再インストール
   ```bash
   npm ci
   ```

3. **エラーログ**: エラーメッセージを確認して原因を特定

### 画像が `<picture>` 化されない

**症状**: ビルド後のHTMLで画像が `<picture>` タグにならない

**原因**: 対象条件を満たしていない

**解決方法**:

- 対象拡張子か確認（JPEG/PNG/GIF）
- 対応WebP/AVIFが `dist` に存在するか確認
- `data:` や外部URLは対象外

### 画像のパスが解決できない

**症状**: ビルド後のページで画像が表示されない

**原因**: 画像パスの解決方法が間違っている

**解決方法**:

- **CSS内の画像**: Sassの `url(...)` 経由で参照しているか確認
- **HTML内の画像**: `ty_theme_image_url()` を使用しているか確認
- `dist/theme-assets.json` に画像のマッピングが含まれているか確認

## デプロイ関連

### FTP接続エラー

**症状**: GitHub ActionsのデプロイがFTP接続エラーで失敗する

**原因**: FTP設定が間違っている

**解決方法**:

1. `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD` を確認
2. FTPサーバーがアクセス可能か確認
3. ファイアウォール設定を確認

### デプロイ先パスエラー

**症状**: デプロイ先のパスが間違っている

**原因**: `FTP_SERVER_DIR` の設定が間違っている

**解決方法**:

- `FTP_SERVER_DIR` が正しいパスか確認
- テーマディレクトリ直下を指定しているか確認

### Discord通知が届かない

**症状**: デプロイ成功/失敗時にDiscord通知が届かない

**原因**: Webhook設定が間違っている

**解決方法**:

1. `DISCORD_WEBHOOK` が正しく設定されているか確認
2. DiscordのWebhookが有効になっているか確認
3. GitHub Actionsのログでエラーを確認

## WordPress関連

### テーマが有効化できない

**症状**: WordPressでテーマを有効化できない

**原因**: テーマヘッダが正しく設定されていない

**解決方法**:

- `style.css` のテーマヘッダを確認
- 必須項目（Theme Name, Version等）が記載されているか確認

### アセットが読み込まれない

**症状**: CSS/JSが読み込まれない

**原因**: Vite連携の設定が間違っている

**解決方法**:

1. `functions-lib/func-vite.php` が正しく読み込まれているか確認
2. `functions-lib/func-vite-assets.php` が正しく読み込まれているか確認
3. dev/prod判定が正しく動作しているか確認
4. `dist/.vite/manifest.json` が存在するか確認（本番環境）

### 画像が表示されない

**症状**: テーマ同梱の画像が表示されない

**原因**: 画像パスの解決方法が間違っている

**解決方法**:

- `ty_theme_image_url()` を使用しているか確認
- `dist/theme-assets.json` に画像のマッピングが含まれているか確認
- 画像ファイルが `src/assets/images/` に存在するか確認

## その他

### メモリ不足エラー

**症状**: ビルド時にメモリ不足エラーが発生する

**原因**: Node.jsのメモリ制限に達している

**解決方法**:

```bash
NODE_OPTIONS="--max-old-space-size=4096" npm run build
```

### ビルドが遅い

**症状**: ビルドに時間がかかる

**原因**: 画像最適化やWebP生成が重い

**解決方法**:

- 不要な画像最適化を無効化
- 代替フォーマットを無効化（`theme-build.config.js` で `imageAltFormats: "none"` — WP テンプレ既定）
- ビルドキャッシュを活用

## 参考資料

- [本ドキュメント（architecture.md）](../architecture.md) — 上記各節に統合済み
## 案件固有情報の記入先

このドキュメントは、案件ごとの仕様を記録するためのファイルです。リポジトリをクローンした後、このファイルの内容を記入してください。

## プロジェクト情報

- **プロジェクト名**: 
- **クライアント名**: 
- **開始日**: 
- **完了日**: 
- **担当者**: 

## プロジェクト初期セットアップ手順

プロジェクト初期セットアップ手順については、[導入・セットアップの詳細](#導入セットアップの詳細) の「プロジェクト初期セットアップ」を参照してください。

## 技術スタック

- **WordPress**: バージョン
- **PHP**: バージョン
- **Node.js**: バージョン
- **Vite**: バージョン
- **その他**: 

## ディレクトリ構成

### 主要ディレクトリ

```
src/assets/
  sass/          # Sassファイル
  js/            # JavaScriptファイル
  images/        # 画像ファイル
  fonts/         # フォントファイル
functions-lib/   # PHP機能ファイル
components/     # PHPコンポーネント
dist/            # ビルド成果物
```

### カスタムディレクトリ

案件固有のディレクトリがあれば記載：

- 

## コンポーネント一覧

### PHPコンポーネント

| ファイル名 | 用途 | 説明 |
|------------|------|------|
| | | |

### JavaScriptモジュール

| ファイル名 | 用途 | 説明 |
|------------|------|------|
| | | |

### Sassコンポーネント

| ファイル名 | 用途 | 説明 |
|------------|------|------|
| | | |

## 投稿タイプ

デフォルト投稿タイプ（post、page）とカスタム投稿タイプの両方を記載します。

| 投稿タイプ | スラッグ | 表示名 | 詳細ページ | タクソノミー | 説明 |
|------------|----------|--------|------------|------------|------|
| デフォルト投稿 | `post` | お知らせ | あり | category, tag |  |
| 制作実績 | `works` | 制作実績 | なし | works_category |  |

## ページ構成

| ページ名 | スラッグ | ページ種別 | 説明 | 備考 |
|----------|----------|------------|------|------|
| | | | | |

## テンプレートファイル

| ページ | ページ種別 | テンプレートファイル | 説明 | 備考 |
|--------|------------|---------------------|------|------|
| トップページ | フロントページ | `front-page.php` | 各セクションで投稿を出力 |  |
| 固定ページ（その他） | 固定ページ | `page.php` | デフォルト固定ページテンプレート | プライバシーポリシー |
| 固定ページ（お問い合わせ） | 固定ページ | `page-contact.php` | お問い合わせページ |  |
| カスタム投稿一覧 | カスタム投稿アーカイブ | `archive.php` | カスタム投稿一覧表示 | カスタム投稿タイプworksのアーカイブ |
| コラム一覧 | デフォルト投稿アーカイブ | `home.php` | コラム一覧表示 |  |
| 個別詳細ページ | お知らせ・コラム共通 | `single.php` |  |  |
| 404エラーページ | エラーページ | `404.php` | ページが見つからない場合のエラーページ |  |
| インデックスページ | フォールバック | `index.php` | その他のページのフォールバック |  |

## 機能一覧（functions-lib）

| ファイル名 | 機能 |
|------------|------|
| `func-admin-menu.php` | 管理画面メニューのカスタマイズ（非表示にするメニュー・トップレベルに表示する固定ページを配列で管理） |
| `func-ai1wm-exclude.php` | All-in-One WP Migrationでエクスポートする際に一部ファイルを除外 |
| `func-base.php` | WordPressの基本的な機能を設定（テーマサポート、コメント無効化、絵文字無効化等） |
| `func-images.php` | 画像関連のヘルパー関数（画像URL取得、画像表示等） |
| `func-modify-youtube-oembed.php` | YouTube埋め込み時のパラメータ調整 |
| `func-nav-items.php` | ナビメニューを一元管理 |
| `func-new-post.php` | 投稿が指定した日数以内であるか判定 |
| `func-no-auto-generate.php` | 意図せず自動生成されるページの表示を防ぐ（404エラーにする） |
| `func-page-editor.php` | 固定ページの本文エディタ表示制御（本文を使わないテンプレートでエディタを非表示） |
| `func-posts-ajax-load-more.php` | アーカイブページの「もっと見る」Ajax読み込み機能 |
| `func-recaptcha.php` | 必要なページでのみreCAPTCHAスクリプトを読み込む |
| `func-security.php` | セキュリティ対策（WordPressバージョン情報の削除） |
| `func-set-posttype-post.php` | デフォルト投稿（post）のラベル変更。冒頭定数 `TY_POST_LABEL_NAME` / `TY_POST_HIDE_CATEGORY_UI` / `TY_POST_HIDE_TAG_UI` |
| `func-set-posttype-works.php` | カスタム投稿タイプ「works」の設定 |
| `func-thumbnail.php` | サムネイル画像の表示とデータ取得関数 |
| `func-url.php` | パス定義のヘルパー関数（img_path、page_path等） |
| `func-vite-assets.php` | Viteアセットの読み込み（enqueue実装） |
| `func-vite.php` | Vite連携（dev/prod判定 + URL解決） |
| `func-wpcf7.php` | Contact Form 7 の autop（`<p>` / `<br>` 自動付与）を全フォームで無効化 |

### Contact Form 7（autop）

- **実装**: `functions-lib/func-wpcf7.php` — `add_filter( 'wpcf7_autop_or_not', '__return_false' );`
- **理由**: フォームは `p-form` BEM（`dl` / `dt` / `dd` / `.p-form__select-wrap` 等）で HTML を組む。CF7 既定の autop だと余計な `<p>` が入りレイアウトが崩れる。
- **スコープ**: サイト内の**全 CF7 フォーム**（`the_content` の `wpautop` とは別）。
- **汎用方針・タグ記述の注意**: ナレッジ [`wiki/wordpress-contact-form-7-autop.md`](file:///Users/yoshiaki/working/2026-04-23kn/wiki/wordpress-contact-form-7-autop.md)
- **送信ボタン**: `.p-form__submit` ラッパー + `input` 本体（詳細は上記 Wiki「送信ボタン」節）。Sass は `src/assets/sass/components/_p-form.scss`。

## 画像最適化設定

- **WebP生成**: 有効/無効
- **JPEG品質**: 
- **PNG品質**: 
- **WebP品質**: 

## デプロイ設定

- **デプロイ先**: 
- **FTPサーバー**: 
- **デプロイディレクトリ**: 
- **Discord通知**: 有効/無効

## 開発環境

- **ローカル環境**: 
- **テスト環境**: 
- **本番環境**: 

## 注意事項

### 変更してはいけない項目

- 

### 案件固有のルール

- 

## 参考資料

- 

## 変更履歴

| 日付 | 変更内容 | 担当者 |
|------|----------|--------|
| | | |

---

## 参考・移行ログ

## 方針（決定事項）
- 開発: **WordPressで表示**しつつ、フロント資産は **Vite dev server(HMR)** を参照する
- 本番: **Vite build成果物（manifest）** から `wp_enqueue_script/style` で読み込む
- 画像: **WordPressネイティブ（サイズ生成・srcset等）を優先**し、静的HTML前提の後処理（after-buildによる`<picture>`化・`width/height`自動付与）は廃止/最小化する

## 仕組みの整理（3分類）

### 引き継げない（削除対象）
- EJS→HTML生成の仕組み
  - `vite-plugin-ejs` / `ViteEjsPlugin()`（`vite.config.js`）
  - EJSテンプレ群（`src/ejs/**`）
- 複数HTMLをエントリにして`dist/**/*.html`を作る仕組み
  - 静的ページ実体（`src/**/*.html`）
  - `globSync("src/**/*.html")` を `rollupOptions.input` に登録（`vite.config.js`）
- HTML後処理（静的HTML前提）
  - `scripts/after-build.mjs`（`dist/**/*.html`を書き換える設計のため、WPテンプレート運用と噛み合わない）
- `dist/` を対象にしたHTML検証
  - `.htmlvalidate.json`
  - `package.json` の `validate:html`（`html-validate dist/`）

### 引き継ぐには修正が必要
- Vite設定: HTMLエントリを廃止し、**JS/CSSエントリ + manifest** へ変更（WP enqueue前提）
- エントリポイント: テンプレ直書き（`/assets/sass/style.scss` 等）をやめ、Viteのエントリからまとめてimport
- ページ設定: `config/site.config.js` の `pages` 管理はWPの「固定ページ/メニュー/SEO」へ置換
- 画像最適化: テーマ同梱画像の最適化が不要なら、Viteのimagemin関連依存を削除して軽量化
- Sass内のパスが静的サイト前提（要修正）
  - `src/assets/sass/base/_root.scss`: `url("/fonts/...")`（サイトルート前提）
  - `src/assets/sass/components/*`: `url(/assets/images/...)` / `mask: url(/assets/images/...)`（サイトルート前提）

### そのまま使える（基本維持）
- SCSS構成（`src/assets/sass/**`）とPostCSS設定（`postcss.config.cjs`）
- JSモジュール群（`src/assets/js/**`）
- 依存ライブラリ（`flatpickr`, `@splidejs/*`, `kiso.css` 等）
- テーマ同梱の画像・フォント（ただし参照パスはWP基準に修正）

## 課題リスト（チェックリスト）
- [x] docs: このファイルに「作業ログ」を残す運用を開始
- [x] 不要物削除: EJS/静的HTML/after-build/静的サイト固有設定を削除
- [x] WP最小テーマ: `style.css` / `functions.php` / `index.php` / `header.php` / `footer.php` を追加
- [x] Vite連携(dev): `@vite/client` + エントリをWPから読み込めるようにする（HMR/フルリロード含む）
- [x] Vite連携(prod): `dist/.vite/manifest.json` を読んでenqueueする（JS由来CSSも含む）
- [x] 参照パス調整: フォント/画像/CSS内URLなどをWP基準に（Viteで解決できる形に整理）
- [x] 依存整理: 不要になったnpm依存（EJS/after-build/HTML検証等）を削除
- [x] ドロワーメニューが機能しない（開閉しない/反応しない）

## 課題の整理（優先度と分類）
### P0（体験/動作を止める）
- [x] メニューの供給元を確定（旧環境同様: `get_nav_items()` を単一の供給元とする）

### P1（移植/運用の中核）
- [ ] 既存phpファイルの引き継ぎ（**functions-lib** の取捨選択と統合）
  - すでに移植済み: 旧テンプレ互換のヘルパー（`functions-lib/func-legacy.php`）、テンプレ（`front-page.php` など）、components（`components/*`）
  - 未移植: CPT/ショートコード/セキュリティ/recaptcha 等（旧テーマ: `functions-lib/*.php`）
- [x] 画像の `width` / `height` 属性（テーマ同梱画像は `ty_img('demo/dummy1.jpg')` のように指定して付与。WPメディアは `wp_get_attachment_image()` を優先）
- [x] 画像の `loading` 属性（default: `lazy`。LCP候補は `eager` + `fetchpriority="high"` を使用）
- [ ] 自動デプロイ
  - [ ] GitHub Actionsの設定（できればコマンドで再現できる形）
  - [x] `dist/` をコミットするかどうか方針決め（CIで `npm run build` → FTP転送するため、`dist/` はコミットしない）

### P2（整理・品質）
- [ ] Huskyを削除（依存・scripts・フック運用）
- [ ] 旧静的サイト資産の残骸を整理（`src/ejs/**` など、使わないものを最終削除）
- [ ] デバッグコード/スタイルの撤去（例: `src/assets/sass/components/_p-drawer.scss` の `background-color: pink;`）
- [ ] WebP生成ポリシーを案件ごとに決める（有効/無効、品質、サイズ比較の扱い）

### ドロワーメニュー不具合（原因候補）※解消済み
- JS側は `#js-menu` / `#js-drawer` / `#js-drawer-menu` が揃わないと初期化を中止します（`src/assets/js/_drawer.js` の `if (!menuButton || !drawer || !drawerMenu) return;`）。
- 現在のWPテンプレは **上記IDを固定で出力**しているため、次は以下を確認する:
  - クリックイベントがバインドされているか（`#js-menu`）
  - `aria-hidden` / `aria-expanded` の切り替えが期待通りか（CSSは `aria-hidden="false"` で表示）
  - `inert` の有無・ブラウザ互換（必要ならpolyfill/撤去）
  - `z-index` / `position` 競合（ヘッダー固定化・管理バー対応と干渉していないか）

## 作業ログ（解消方法の記録）
### YYYY-MM-DD
- 変更:
- 目的:
- 方法:
- 影響範囲:

### 2025-12-24
- 変更: `scripts/after-build.mjs` を削除、`.htmlvalidate.json` を削除、`package.json` から `validate:*` と after-build 実行を撤去
- 目的: WP運用では `dist/**/*.html` 前提の後処理/検証が成立しないため（画像はWPネイティブ優先）
- 方法: ファイル削除 + npm scripts整理
- 影響範囲: `npm run build` の挙動（HTML後処理なし）、HTML検証コマンド廃止

### 2025-12-24
- 変更: クラシックテーマ最小ファイル追加（`style.css`, `functions.php`, `header.php`, `footer.php`, `index.php`）
- 目的: WPで表示できる最小テーマ骨格を用意
- 方法: `wp_head()`/`wp_footer()`/`the_content()` の最小構成
- 影響範囲: テーマとして有効化可能

### 2025-12-24
- 変更: Vite連携の土台追加（`functions-lib/func-vite.php`, `functions.php`でenqueue、`vite.config.js`をJS/CSSエントリ+manifestへ移行）
- 目的: 開発（HMR）/本番（manifest）で同一エントリを読み込むため
- 方法: devは`@vite/client`とエントリを読み込み、prodは`dist/manifest.json`からCSS/JSをenqueue
- 影響範囲: `vite`のビルド対象がHTMLからJSエントリへ変更

### 2025-12-24
- 変更: Sass内の絶対パス参照を相対パスに変更（`/fonts`・`/assets/images`）
- 目的: WPではサイトルート直下に`/assets`や`/fonts`が存在する前提にできないため（Viteビルドで解決させる）
- 方法: `src/assets/sass/**` の `url(...)` / `mask: url(...)` を `src/` 配下への相対参照へ変更
- 影響範囲: CSSから参照するフォント/画像の解決方法

### 2025-12-24
- 変更: EJSテンプレ群と静的HTMLを削除（`src/ejs/**`, `src/**/*.html`）、静的サイト用設定を削除（`config/site.config.js`, `config/utils.js`）
- 目的: WPテーマ運用では不要・混乱要因になるため
- 方法: ファイル削除
- 影響範囲: 静的サイトとしてのビルド/プレビューは不可（WPテーマとして運用）

### 2025-12-24
- 変更: dev server判定とenqueueを調整（HTTPS環境を考慮、dev時はエントリをheadで読み込み）
- 目的: HTTPSのWP表示でMixed Contentを避けつつ、dev時のCSS適用遅れ（FOUC）を抑える
- 方法: `TY_VITE_DEV_SERVER`をHTTPS優先で定義、dev判定の`wp_remote_head`で`sslverify=false`、dev時のエントリscriptをheadで読み込み
- 影響範囲: dev時の読み込み順、Vite dev serverのURL指定

### 2025-12-24
- 変更: CSSをJS importではなく「CSSエントリ」として読み込む方式へ変更
- 目的: dev時にCSSを`<link>`で先に適用しやすくするため
- 方法: Viteのrollup inputに `style.scss` を追加し、WP側はdev/prodで`style.scss`をenqueue、JSは`main.js`のみenqueue
- 影響範囲: manifestのエントリ、WP enqueueの関数名

### 2025-12-24
- 変更: `entrypoints/` を廃止し、Vite/WPのエントリを `src/assets/js/main.js` と `src/assets/sass/style.scss` に統一
- 目的: 入口ファイル（entrypoints）を挟まず、最小構成で運用するため
- 方法: `vite.config.js` の `rollupOptions.input` と `functions.php` のenqueue指定を差し替え、不要な `src/entrypoints/*` を削除
- 影響範囲: manifestのキー、WP enqueue指定

### 2025-12-24
- 変更: Sass内の相対パスを `style.scss` 基準に修正、フォントを `src/assets/fonts` に移動
- 目的: `vite build` で画像/フォントの参照が解決できず runtime に残ってしまう問題を避けるため
- 方法: `url("../../images/...")` → `url("../images/...")`、フォントは `src/public/fonts` → `src/assets/fonts` へ移動し `url("../fonts/...")` に統一
- 影響範囲: `background-image` / `mask` / `@font-face` の参照先

### 2025-12-24
- 変更: JS entryに紐づくCSS（例: ライブラリCSS）もprodでenqueue
- 目的: `src/assets/js/main.js` がimportしているCSSが本番で反映されない状態を解消
- 方法: `dist/.vite/manifest.json` の `css` 配列を読み、`wp_enqueue_style` で追加読み込み
- 影響範囲: 本番のCSS読み込み本数（`main-*.css` が追加される）

### 2025-12-24
- 変更: 旧テーマ（`t_2025-01-11wp`）のPHP資産を一部移植（テンプレ/コンポーネント/ヘルパー）
- 目的: 既存のPHPテンプレ資産を新テーマで動かしながら移行できるようにするため
- 方法:
  - `functions-lib/func-legacy.php` を追加し、旧テンプレ互換の関数（`page_path`, `img_path`, `get_nav_items`, `display_thumbnail` 等）を提供
  - `header.php` / `footer.php` を旧テーマのクラス・構造に寄せて更新
  - `front-page.php` / `page.php` / `single.php` / `archive.php` / `home.php` / `page-contact.php` / `404.php` を追加
  - 不足していた `components/p-pagenavi.php` は簡易版を新規作成
- 影響範囲:
  - テンプレ互換のため関数名は旧テーマ準拠（将来的に整理する余地あり）
  - 画像URLは `ty_theme_asset_url()` 経由で dev/prod を吸収

## 開発（HMR）確認手順（HTTPSのWP）
### 前提
- WP表示URLがHTTPSの場合、Vite dev serverもHTTPSで配信しないとMixed Contentでブロックされる

### 手順（最小）
- テーマ直下に `.certs/` を作り、以下を配置
  - `localhost.pem`
  - `localhost-key.pem`
- `npm run dev`
- WPページのソースで以下を確認
  - `@vite/client` が読み込まれている
  - CSSが `https://localhost:5173/src/assets/sass/style.scss` から読み込まれている
  - JSが `https://localhost:5173/src/assets/js/main.js` から読み込まれている

## PHP変更の即時反映（dev時）
- PHPはHMRで差し替えできないため、**PHPファイル変更を検知してブラウザをフルリロード**する
- `vite.config.js` にPHP変更で `full-reload` を投げる処理を追加済み

## 画像代替フォーマットの制御（案件ごと）
[`config/theme-build.config.js`](config/theme-build.config.js) が正本です（詳細は [画像最適化（ビルド時）](#画像最適化ビルド時)）。環境変数での上書きは行いません。WP テンプレ既定は `imageAltFormats: "none"`（プラグイン想定）。案件で Vite 側の AVIF/WebP が必要なときだけ `webp` / `avif` / `both` に変更して再ビルドします。

### 2026-06-04
- 変更: `config/theme-build.config.js` によるビルド設定一本化（`imageAltFormats` / `useFileHash` / `cssMinify`）、`ty_img` の format `<picture>`、`postbuild-image-set.mjs`
- 既定: `imageAltFormats: "none"`、`useFileHash: true`、`cssMinify: true`
- 影響: `vite.config.js`、`func-images.php`、`func-vite.php`、`package.json`（`imagemin-avif`、build 後処理）

### 2025-12-24
- 変更: prod側のmanifest参照パスを `dist/.vite/manifest.json` に追従（`functions-lib/func-vite.php`）
- 目的: `vite build` の実際の出力先に合わせて、WPが正しくhashファイルをenqueueできるようにするため
- 方法: `ty_vite_manifest_path()` を `dist/.vite/manifest.json` 優先 + `dist/manifest.json` フォールバックに変更
- 影響範囲: 本番（build成果物）の読み込み

## 削除予定リスト（確定したらチェック）
### 静的HTML（WP移行後は不要）
- `src/index.html`
- `src/contact/index.html`
- `src/contact/thanks.html`
- `src/privacy/index.html`
- `src/demo/index.html`
- `src/demo/demo-accordion/index.html`
- `src/demo/demo-css-animation/index.html`
- `src/demo/demo-dialog/index.html`
- `src/demo/demo-fadein/index.html`
- `src/demo/demo-grid-layout/index.html`
- `src/demo/demo-hover-button/index.html`
- `src/demo/demo-hover-card/index.html`
- `src/demo/demo-hover-change/index.html`
- `src/demo/demo-hover-text/index.html`
- `src/demo/demo-splide/index.html`

### EJS
- `src/ejs/common/_head.ejs`
- `src/ejs/common/_header.ejs`
- `src/ejs/common/_footer.ejs`
- `src/ejs/components/*`（全14ファイル）
- `src/ejs/data/*`

### build後処理（静的HTML前提）
- `scripts/after-build.mjs`

### 静的サイト用のページ設定
- `config/site.config.js`
- `config/utils.js`
