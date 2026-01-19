# t2025-12-24vite-wp（WordPressテーマ + Vite）

WordPress（クラシックテーマ）用の制作環境です。

## 特徴

- **開発**: WordPressで表示しつつ、CSS/JSは Vite dev server（HMR）を参照
- **本番**: `npm run build` で `dist/` を生成し、WordPress側で読み込み
- **自動デプロイ**: GitHub ActionsでFTP経由の自動デプロイに対応

## 最短導入

```bash
# 1. 依存関係をインストール
npm install

# 2. 開発サーバーを起動
npm run dev

# 3. 本番ビルド
npm run build
```

詳細な導入手順は [docs/00-getting-started/quick-start.md](docs/00-getting-started/quick-start.md) を参照してください。

## リポジトリのクローン（テンプレートから）

GitHub CLIを使って、テンプレートリポジトリから新規リポジトリを作成・クローンできます：

```bash
gh repo create 新規リポジトリ名 \
  --template rea1i2e/t2025-12-24vite-wp \
  --private \
  --description "リポジトリの説明文" && \
sleep 5 && \
gh repo clone GitHubのユーザー名/新規リポジトリ名
```

## ドキュメント

### 初めて使う人向け

1. **[docs/00-getting-started/README.md](docs/00-getting-started/README.md)**: はじめに（読む順番ガイド）
2. **[docs/00-getting-started/quick-start.md](docs/00-getting-started/quick-start.md)**: 最短導入（すぐに始めたい人向け）
3. **[docs/00-getting-started/setup.md](docs/00-getting-started/setup.md)**: 詳細セットアップ手順（新規案件作成時）

### 開発者向け

- **[docs/01-development/README.md](docs/01-development/README.md)**: 開発ガイドの目次
- **[docs/01-development/architecture.md](docs/01-development/architecture.md)**: 設計判断・守るルール（MUST/SHOULD/MAY）
- **[docs/01-development/coding-standards.md](docs/01-development/coding-standards.md)**: コーディング規約
- **[docs/01-development/development.md](docs/01-development/development.md)**: 開発フロー・スクリプト

### デプロイ関連

- **[docs/02-deployment/README.md](docs/02-deployment/README.md)**: デプロイガイドの目次
- **[docs/02-deployment/deploy.md](docs/02-deployment/deploy.md)**: デプロイ手順

### トラブルシューティング

- **[docs/03-troubleshooting/troubleshooting.md](docs/03-troubleshooting/troubleshooting.md)**: トラブルシューティング

### 案件固有情報

- **[docs/04-project-specific/README.md](docs/04-project-specific/README.md)**: 案件情報の記入ガイド
- **[docs/04-project-specific/overview.md](docs/04-project-specific/overview.md)**: 案件仕様（テンプレート）

### 意思決定ログ

- **[docs/05-decisions/README.md](docs/05-decisions/README.md)**: ADRの説明
- **[docs/05-decisions/](docs/05-decisions/)**: 意思決定ログ（ADR形式）

### 参考資料

- **[docs/06-reference/README.md](docs/06-reference/README.md)**: 参考資料の目次
- **[docs/06-reference/migration-log.md](docs/06-reference/migration-log.md)**: WordPress移行メモ（参考用）

### AIエージェント向け

- **[AGENTS.md](AGENTS.md)**: AIエージェント向けプロジェクト理解ガイド（Cursor / ChatGPT が参照）

## ドキュメントマップ

### 読む順番（初めて使う人）

1. **セットアップ**: [docs/00-getting-started/](docs/00-getting-started/) から始める
2. **開発を始める**: [docs/01-development/](docs/01-development/) を参照
3. **案件情報を記入**: [docs/04-project-specific/](docs/04-project-specific/) に記入

### 読むタイミング

- **新規案件作成時**: [docs/00-getting-started/setup.md](docs/00-getting-started/setup.md)
- **開発中**: [docs/01-development/](docs/01-development/)
- **デプロイ時**: [docs/02-deployment/deploy.md](docs/02-deployment/deploy.md)
- **問題が発生した時**: [docs/03-troubleshooting/troubleshooting.md](docs/03-troubleshooting/troubleshooting.md)

### 人間向け vs AI向け

- **人間が読む場合**: 各セクションのREADME.mdから始める
- **AIエージェント（Cursor / ChatGPT）**: [AGENTS.md](AGENTS.md) を参照

## ライセンス

プロジェクトに合わせて追記してください。
