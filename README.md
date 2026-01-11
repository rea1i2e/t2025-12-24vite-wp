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

詳細な導入手順は [docs/setup.md](docs/setup.md) を参照してください。

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

> 注意: `rea1i2e/t2025-12-24vite-wp` は実際のテンプレートリポジトリ名に置き換えてください。

## ドキュメント

- **[AGENTS.md](AGENTS.md)**: AIエージェント向けプロジェクト理解ガイド
- **[docs/architecture.md](docs/architecture.md)**: 設計判断・守るルール（MUST/SHOULD/MAY）
- **[docs/setup.md](docs/setup.md)**: 導入手順
- **[docs/development.md](docs/development.md)**: 開発ガイド
- **[docs/deploy.md](docs/deploy.md)**: デプロイ手順
- **[docs/troubleshooting.md](docs/troubleshooting.md)**: トラブルシューティング
- **[docs/decisions/](docs/decisions/)**: 意思決定ログ（ADR形式）
- **[docs/overview.md](docs/overview.md)**: 案件仕様

## ライセンス

プロジェクトに合わせて追記してください。
