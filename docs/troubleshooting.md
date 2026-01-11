# トラブルシューティング

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
- WebP生成を無効化（`VITE_ENABLE_WEBP=false`）
- ビルドキャッシュを活用

## 参考資料

- [docs/setup.md](setup.md): セットアップ手順
- [docs/development.md](development.md): 開発ガイド
- [docs/deploy.md](deploy.md): デプロイ手順
- [docs/architecture.md](architecture.md): 設計判断・守るルール

