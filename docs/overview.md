# プロジェクト概要

このドキュメントは、案件ごとの仕様を記録するためのファイルです。リポジトリをクローンした後、このファイルの内容を記入してください。

## プロジェクト情報

- **プロジェクト名**: 
- **クライアント名**: 
- **開始日**: 
- **完了日**: 
- **担当者**: 

## プロジェクト初期セットアップ手順

1. **LOCALのセットアップ**
   - [docs/setup.md](setup.md) を参照

2. **リポジトリ複製**
   - GitHub CLIを使ってテンプレートリポジトリから新規リポジトリを作成・クローン：
     ```bash
     gh repo create 新規リポジトリ名 \
       --template rea1i2e/t2025-12-24vite-wp \
       --private \
       --description "リポジトリの説明文" && \
     sleep 5 && \
     gh repo clone GitHubのユーザー名/新規リポジトリ名
     ```
   - テンプレートリポジトリ名は実際のリポジトリ名に置き換えること

3. **プロジェクトID一括置換**
   - プロジェクト固有のIDを一括置換
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
     - `docs/development.md`（コマンド例）
     - `docs/overview.md`（コマンド例）
     - `docs/setup.md`（ディレクトリ名）
     - `tools/README.md`（コマンド例）
   - 注意: 以下のMDファイルはテンプレートとして残します（置換しません）
     - `README.md`
     - `docs/deploy.md`
     - `docs/architecture.md`
     - `docs/decisions/README.md`
     - `docs/troubleshooting.md`
   - **個別に変更しても問題ない項目**：
     - `style.css` の `Theme Name`: WordPress管理画面に表示されるテーマ名なので、案件名などに変更しても問題ありません
   - **テーマディレクトリ名を変更する場合の注意**：
     - テーマディレクトリ名を変更する場合は、GitHub Secrets の `FTP_SERVER_DIR` を更新してください（サーバー側のディレクトリ名と一致させる必要があります）
     - `.github/workflows/deploy.yml` には影響ありません（コメント内の例のみ）
     - `tools/` 配下のパスは一括置換で自動更新されます

4. **Secrets指定、登録**
   - [docs/deploy.md](deploy.md) の「Secrets設定のスクリプト化」を参照

5. **サーバー管理パネルからの作業**
   1. サブディレクトリ作成
   2. サブFTPアカウント作成
   3. Basic認証設定
   4. WordPressインストール

6. **固定ページ登録**
   - `tools/pages.json` を編集して固定ページを定義
   - Local の Site Shell で `wp eval-file wp-content/themes/t2025-12-24vite-wp/tools/import-pages.php wp-content/themes/t2025-12-24vite-wp/tools/pages.json upsert` を実行
   - 詳細は [tools/README.md](../tools/README.md) を参照

7. **フォントの登録**
   1. デザインデータから使用フォントを確認
   2. 必要なフォントファイルをダウンロード
   3. フォントファイルを圧縮（woff2形式推奨）
   4. `src/assets/fonts/` に配置
   5. `src/assets/sass/base/_root.scss` の `@font-face` を編集
   6. `header.php` の `preload` リンクを追加（必要に応じて）

8. **カスタムプロパティ・Sass変数の登録**
   1. `src/assets/sass/global/_setting.scss` でSass変数を定義
      - `$inner-sp`、`$inner-pc`（コンテンツ幅）
      - `$padding-sp`、`$padding-pc`（パディング）
      - その他のレイアウト値
   2. `src/assets/sass/base/_root.scss` の `:root` 内でカスタムプロパティを定義
      - 色（`--color-theme`、`--color-accent` など）
      - サイズ（`--header-height` など）
      - その他のデザイン値

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

## カスタム投稿タイプ

| 投稿タイプ | スラッグ | 表示名 | 詳細ページ | タクソノミー | 説明 |
|------------|----------|--------|------------|------------|------|
| | | | | | |

## テンプレートファイル

| ページ | ページ種別 | テンプレートファイル | 説明 | 備考 |
|--------|------------|---------------------|------|------|
| | | | | |

## 機能一覧

### WordPress機能

- 

### カスタム機能

- 

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

