# デプロイ手順

このドキュメントでは、GitHub Actionsを使った自動デプロイの設定方法を説明します。

## 概要

GitHub ActionsでFTP経由の自動デプロイを実行します。

- **トリガー**: `main`/`master` ブランチへのpush、PR作成
- **デプロイ先**: FTPサーバー
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

### 任意

- `DISCORD_WEBHOOK`: Discord通知を使う場合のWebhook URL
- `TEST_URL`: デプロイ通知/サマリーに表示するURL

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
5. **デプロイサマリー生成**: GitHub Actionsのサマリーにデプロイ情報を出力
6. **FTPデプロイ**: `SamKirkland/FTP-Deploy-Action` を使用してFTPサーバーにアップロード
7. **Discord通知**: デプロイ成功/失敗時にDiscordに通知（オプション）

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
- `node_modules/`, `src/`, `scripts/`, `docs/`
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

詳細は [docs/troubleshooting.md](troubleshooting.md) を参照してください。

