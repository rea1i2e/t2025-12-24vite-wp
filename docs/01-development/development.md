# 開発ガイド

このドキュメントでは、開発時の作業フローやスクリプトの使い方を説明します。

## 開発フロー

1. `npm run dev` を実行してVite dev serverを起動
2. WordPressでテーマを有効化
3. `src/` 以下を編集（Sass/JS/画像/フォント）
4. ブラウザで自動反映を確認（HMR対応）

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
- WebP生成（環境変数 `VITE_ENABLE_WEBP=true` で有効化）
- 画像圧縮（JPEG/PNG/GIF/SVG）
- CSS/JSのハッシュ付与

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

### WebP生成

環境変数で制御可能：

```bash
VITE_ENABLE_WEBP=true npm run build
```

設定項目：
- `VITE_ENABLE_WEBP`: WebP生成の有効/無効（デフォルト: `false`）
- `VITE_WEBP_QUALITY`: WebPの品質（デフォルト: `75`）
- `VITE_WEBP_SKIP_IF_LARGER`: 最適化後より大きいWebPは出力しない（デフォルト: `true`）

### 出力パス

- 画像: `assets/images/[name]-[hash][ext]`
- CSS: `assets/css/[name]-[hash].css`
- JS: `assets/js/[name]-[hash].js`

## PHP開発

### テンプレートファイル

WordPressのテンプレート階層に従ってファイルを配置します。

- `front-page.php`: トップページ
- `page.php`: 固定ページ
- `single.php`: 個別投稿
- `archive.php`: アーカイブ
- `404.php`: 404エラーページ

### コンポーネント

再利用可能なPHPコンポーネントは `components/` 配下に配置します。

```php
<?php
// components/c-button.php
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

詳細は [architecture.md](architecture.md) を参照してください。

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

開発時に問題が発生した場合は、[docs/03-troubleshooting/troubleshooting.md](../03-troubleshooting/troubleshooting.md) を参照してください。

