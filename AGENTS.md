# AIエージェント向けプロジェクト理解ガイド

このドキュメントは、AIエージェント（Cursor / ChatGPT）がこのプロジェクトを理解し、適切なコード変更や提案を行うためのガイドです。

**人間が読む場合は、[docs/01-development/README.md](docs/01-development/README.md) を参照してください。**

## 技術スタック

- **WordPress**: クラシックテーマ（PHPテンプレート）
- **Vite**: フロントエンドビルドツール（HMR対応）
- **Sass**: CSSプリプロセッサ
- **PostCSS**: CSS後処理（autoprefixer、メディアクエリソート）

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

## 重要な判断基準

### Vite連携

- **エントリポイント**: `src/assets/js/main.js`（JS）、`src/assets/sass/style.scss`（CSS）
- **dev/prod判定**: `functions-lib/func-vite.php` の `ty_vite_is_dev()` で判定
  - dev: Vite dev server（`localhost:5173`）に到達可能か確認
  - prod: `dist/.vite/manifest.json` を参照
- **アセット読み込み**: `functions-lib/func-vite-assets.php` で `wp_enqueue_style/script` を実装

詳細は [docs/01-development/architecture.md](docs/01-development/architecture.md) の「Vite連携の詳細」を参照してください。

### アセット管理

- **画像（CSS内）**: `src/assets/images/**` を Sass の `url(...)` 経由で参照
- **画像（HTML内）**: `src/assets/images/**` を `dist/assets/images/**` へ出力し、`dist/theme-assets.json` でマッピング
- **フォント**: `src/assets/fonts/**` を `@font-face` で参照
- **JS/CSS**: Viteのmanifestでハッシュ付きファイル名を解決

### デプロイ方針

- **開発用ファイルはデプロイしない**: `src/`, `docs/`, `scripts/`, `node_modules/` などは除外
- **ビルド成果物のみデプロイ**: `dist/`, `*.php`, `style.css` など
- **自動デプロイ**: GitHub ActionsでFTP経由のデプロイを実行

## コーディング規約

詳細は [docs/01-development/coding-standards.md](docs/01-development/coding-standards.md) を参照してください。

### 要点

- **PHP関数名**: `ty_` プレフィックスを必ず付与
- **PHP配列**: `array()` ではなく `[]` を使用
- **PHPデータ取得とHTML**: 可能な限り分けて記述
- **Sass margin**: `margin-block-start` のみを使用
- **Sass入れ子**: 基本的にフラットな構造を維持（BEMクラス名は入れ子にしない）

## 設計判断（MUST/SHOULD/MAY）

詳細は [docs/01-development/architecture.md](docs/01-development/architecture.md) を参照してください。

### MUST（変えてはいけないルール）

- **Vite連携の仕組み**: dev/prod判定とmanifest読み込みの仕組みは必須
- **関数名のプレフィックス**: `ty_` を必ず付与（WordPress/プラグインとの衝突回避）

### SHOULD（推奨ルール）

- **ディレクトリ構成**: `functions-lib/`, `components/`, `src/assets/`, `dist/` の使用を推奨
- **画像の扱い**: CSS内はViteが解決、HTML内は `ty_theme_image_url()` を使用

### MAY（状況次第で変えていい判断）

- **画像最適化**: 環境変数で制御可能
- **カスタム投稿タイプ**: 案件に応じて追加可能

## ディレクトリ構成の要点

```
src/assets/          # 開発用ソース（Sass/JS/画像/フォント）
dist/                # ビルド成果物（ハッシュ付きアセット + manifest）
functions-lib/       # PHP機能ファイル（各機能を分割）
components/          # PHPコンポーネント（再利用可能な部品）
```

## 変更時の注意点

- **Vite連携の仕組みを変更しない**: dev/prod判定とmanifest読み込みの仕組みは必須
- **関数名のプレフィックス**: `ty_` を必ず付与（WordPress/プラグインとの衝突回避）
- **画像パスの解決**: CSS内はViteが解決、HTML内は `ty_theme_image_url()` を使用
- **スクリプトの実行方法**: 実行権限を付与せず、`bash`コマンドで実行する方法を推奨
  - 理由: クロスプラットフォーム対応、Git管理の簡素化、初回セットアップの削減
  - 例: `bash scripts/font-compress.sh input.ttf output.woff2`
  - 各スクリプトのREADMEでは`bash`コマンドのみを記載し、実行権限付与の方法は記載しない

## 参考ドキュメント

- **設計判断・守るルール**: [docs/01-development/architecture.md](docs/01-development/architecture.md)
- **コーディング規約**: [docs/01-development/coding-standards.md](docs/01-development/coding-standards.md)
- **開発ガイド**: [docs/01-development/development.md](docs/01-development/development.md)

