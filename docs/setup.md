# 導入手順

このドキュメントでは、プロジェクトのセットアップ手順を説明します。

## 必要要件

### システム要件

- **Node.js**: 必要（バージョン要件は未確認）
- **npm**: 必要（バージョン要件は未確認）
- **メモリ**: 4GB以上（推奨: 8GB以上）

### プラットフォーム別追加要件

- **macOS**: Xcode Command Line Tools
  ```bash
  xcode-select --install
  ```
- **Windows**: Visual Studio Build Tools または Visual Studio Community
- **Linux**: build-essential
  ```bash
  sudo apt-get install build-essential
  ```

## 環境確認方法

セットアップ前に、必要なツールがインストールされているか確認してください。

### Node.js の確認

```bash
node --version
```

インストールされていることを確認してください（バージョン要件は未確認）。

### npm の確認

```bash
npm --version
```

インストールされていることを確認してください（バージョン要件は未確認）。

### プラットフォーム別確認

#### macOS

Xcode Command Line Toolsがインストールされているか確認：

```bash
xcode-select -p
```

`/Library/Developer/CommandLineTools` が表示されればOKです。

#### Windows

Visual Studio Build Toolsがインストールされているか確認：
- コントロールパネル > プログラムと機能 で確認

#### Linux

build-essentialがインストールされているか確認：

```bash
dpkg -l | grep build-essential
```

build-essential が表示されればOKです。

## セットアップ手順

### 1. Node.js のインストール

Node.jsをインストールしてください（バージョン要件は未確認）。

- [Node.js公式サイト](https://nodejs.org/) からダウンロード
- または、[nvm](https://github.com/nvm-sh/nvm) を使用してインストール

### 2. リポジトリのクローン

```bash
git clone <リポジトリURL>
cd t2025-12-24vite-wp
```

### 3. 依存関係のインストール

```bash
npm install
```

### 4. 開発サーバーの起動

```bash
npm run dev
```

Vite dev serverが起動し、`localhost:5173` でアクセス可能になります。

### 5. WordPressでの確認

1. WordPressのテーマとして有効化
2. ブラウザでWordPressサイトにアクセス
3. 開発者ツールで、CSS/JSが `localhost:5173` から読み込まれていることを確認

## トラブルシューティング

セットアップ時に問題が発生した場合は、[docs/troubleshooting.md](troubleshooting.md) を参照してください。

よくある問題：

- `sharp` のビルド/インストール失敗
  - macOS: Xcode Command Line Tools の導入を確認
  - Node のメジャー更新後は `npm rebuild sharp` を試す
- 権限エラー
  - `dist/` やプロジェクトルートの書き込み権限を確認

