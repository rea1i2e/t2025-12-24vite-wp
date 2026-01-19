# 最短導入

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

より詳細なセットアップ手順が必要な場合は、[setup.md](setup.md) を参照してください。

## トラブルシューティング

問題が発生した場合は、[03-troubleshooting/troubleshooting.md](../03-troubleshooting/troubleshooting.md) を参照してください。
