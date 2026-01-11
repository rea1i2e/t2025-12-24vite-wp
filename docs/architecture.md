# 設計判断・守るルール

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

### function_exists の使用ルール

- **プラグイン関数のチェック**: プラグインで定義されている関数を使うときのみ `function_exists` を使用してください
- **子テーマでの上書きを想定しない**: 子テーマを想定しないため、関数定義の重複防止のための `function_exists` は不要です
- **同一テーマ内の関数への依存**: 同じテーマ内の関数を参照する場合、`function_exists` チェックは不要です（ファイル読み込み順序で解決）

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

### ファイル読み込み順序

- **`functions.php` の `$ordered`**: 依存関係があるファイルのみ `$ordered` 配列に追加することを推奨します
- 通常は空でOKですが、トップレベルで別ファイルの関数/定数を参照する場合は順序指定が必要です

### コーディングスタイル

- **型宣言**: PHP関数には型宣言を付与することを推奨します（`declare(strict_types=1);` を使用）
- **コメント**: 関数の目的や引数の説明をコメントで記載することを推奨します

### 画像の扱い

- **CSS内の画像**: `src/assets/images/**` を Sass の `url(...)` 経由で参照することを推奨します
- **HTML内の画像**: `ty_theme_image_url()` を使用して画像URLを取得することを推奨します
- **WordPressメディア**: WordPressのメディアライブラリを使用する場合は `wp_get_attachment_image()` を優先することを推奨します

## MAY（状況次第で変えていい判断）

案件ごとに判断して良い項目です。

### 画像最適化

- **WebP生成**: 環境変数 `VITE_ENABLE_WEBP` で制御可能です（デフォルト: `false`）
- **画像圧縮**: `@vheemstra/vite-plugin-imagemin` の設定を変更可能です
- **品質設定**: `VITE_JPEG_QUALITY`, `VITE_WEBP_QUALITY` で調整可能です

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

## 詳細情報

### ファイル構成詳細

詳細なファイル構成と実現方法については、以下のセクションを参照してください。

#### テンプレートファイル一覧

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

#### カスタム投稿タイプ

| 投稿タイプ | スラッグ | 表示名 | 詳細ページ | タクソノミー | 説明 |
|------------|----------|--------|------------|------------|------|
| デフォルト投稿 | `post` | お知らせ | あり | category, tag |  |
| 制作実績 | `works` | 制作実績 | なし | works_category |  |

#### 機能ファイル（functions-lib）

| ファイル名 | 機能 |
|------------|------|
| `func-ai1wm-exclude.php` | All-in-One WP Migrationでエクスポートする際に一部ファイルを除外 |
| `func-base.php` | WordPressの基本的な機能を設定（テーマサポート、コメント無効化、絵文字無効化等） |
| `func-images.php` | 画像関連のヘルパー関数（画像URL取得、画像表示等） |
| `func-modify-youtube-oembed.php` | YouTube埋め込み時のパラメータ調整 |
| `func-nav-items.php` | ナビメニューを一元管理 |
| `func-new-post.php` | 投稿が指定した日数以内であるか判定 |
| `func-no-auto-generate.php` | 意図せず自動生成されるページの表示を防ぐ（404エラーにする） |
| `func-recaptcha.php` | 必要なページでのみreCAPTCHAスクリプトを読み込む |
| `func-security.php` | セキュリティ対策（WordPressバージョン情報の削除） |
| `func-set-posttype-post.php` | デフォルト投稿タイプ（post）の設定 |
| `func-set-posttype-works.php` | カスタム投稿タイプ「works」の設定 |
| `func-thumbnail.php` | サムネイル画像の表示とデータ取得関数 |
| `func-url.php` | パス定義のヘルパー関数（img_path、page_path等） |
| `func-vite-assets.php` | Viteアセットの読み込み（enqueue実装） |
| `func-vite.php` | Vite連携（dev/prod判定 + URL解決） |

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

### 画像・フォントの扱い

- 画像（背景画像等）: `src/assets/images/**` を Sass の `url(...)` 経由で参照し、Viteがビルド対象として解決
- フォント: `src/assets/fonts/**` を `@font-face` で参照し、ビルドで `dist/assets/*.woff2` に出力

補足（`<img>` の画像）:
- `<img>` はCSSの `url(...)` のように参照を辿れないため、`vite build` 時に `src/assets/images/**` を `dist/assets/images/**` へ出力し、`dist/theme-assets.json` を生成してPHPが解決する方式を採用
