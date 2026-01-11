# AIエージェント向けプロジェクト理解ガイド

このドキュメントは、AIエージェントがこのプロジェクトを理解し、適切なコード変更や提案を行うためのガイドです。

## 技術スタック

- **WordPress**: クラシックテーマ（PHPテンプレート）
- **Vite**: フロントエンドビルドツール（HMR対応）
- **Sass**: CSSプリプロセッサ
- **PostCSS**: CSS後処理（autoprefixer、メディアクエリソート）

## アーキテクチャ概要

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

### アセット管理

- **画像（CSS内）**: `src/assets/images/**` を Sass の `url(...)` 経由で参照
- **画像（HTML内）**: `src/assets/images/**` を `dist/assets/images/**` へ出力し、`dist/theme-assets.json` でマッピング
- **フォント**: `src/assets/fonts/**` を `@font-face` で参照
- **JS/CSS**: Viteのmanifestでハッシュ付きファイル名を解決

### デプロイ方針

- **開発用ファイルはデプロイしない**: `src/`, `docs/`, `scripts/`, `node_modules/` などは除外
- **ビルド成果物のみデプロイ**: `dist/`, `*.php`, `style.css` など
- **自動デプロイ**: GitHub ActionsでFTP経由のデプロイを実行

## コーディング規約の要点

- **関数名**: テーマ内のPHP関数には `ty_` プレフィックスを付与
- **function_exists**: プラグイン関数のチェックのみ使用（子テーマ想定なし、同一テーマ内関数への依存チェックは不要）
- **ファイル読み込み順序**: `functions.php` の `$ordered` 配列で依存関係を管理

詳細は [docs/architecture.md](docs/architecture.md) を参照してください。

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

