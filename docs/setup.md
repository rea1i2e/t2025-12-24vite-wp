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

## プロジェクト初期セットアップ

テンプレートリポジトリから新規プロジェクトを作成する手順です。

### 1. LOCALのセットアップ

Local環境のセットアップを行います。
サイト名には案件idを使う

### 2. リポジトリ複製

GitHub CLIを使ってテンプレートリポジトリから新規リポジトリを作成・クローン：

```bash
gh repo create 新規リポジトリ名 \
  --template rea1i2e/t2025-12-24vite-wp \
  --private \
  --description "リポジトリの説明文" && \
sleep 5 && \
gh repo clone GitHubのユーザー名/新規リポジトリ名
```

- テンプレートリポジトリ名は実際のリポジトリ名に置き換えること

### 3. プロジェクトID一括置換

プロジェクト固有のIDを一括置換します。

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

### 4. Secrets指定、登録

[docs/deploy.md](deploy.md) の「Secrets設定のスクリプト化」を参照

### 5. サーバー管理パネルからの作業

1. サブディレクトリ作成
2. サブFTPアカウント作成
3. Basic認証設定
4. WordPressインストール

### 6. 固定ページ登録

- `tools/pages.json` を編集して固定ページを定義
- Local の Site Shell で `wp eval-file wp-content/themes/<案件id>/tools/import-pages.php wp-content/themes/<案件id>/tools/pages.json upsert` を実行
  - `<案件id>` は「3. プロジェクトID一括置換」で設定した案件idに置き換えること
- 詳細は [tools/README.md](../tools/README.md) を参照

### 7. フォントの登録

1. デザインデータから使用フォントを確認
2. 必要なフォントファイルをダウンロード
3. フォントファイルを圧縮（woff2形式推奨）
4. `src/assets/fonts/` に配置
5. `src/assets/sass/base/_root.scss` の `@font-face` を編集
6. `header.php` の `preload` リンクを追加（必要に応じて）

### 8. カスタムプロパティ・Sass変数の登録

1. `src/assets/sass/global/_setting.scss` でSass変数を定義
   - `$inner-sp`、`$inner-pc`（コンテンツ幅）
   - `$padding-sp`、`$padding-pc`（パディング）
   - その他のレイアウト値
2. `src/assets/sass/base/_root.scss` の `:root` 内でカスタムプロパティを定義
   - 色（`--color-theme`、`--color-accent` など）
   - サイズ（`--header-height` など）
   - その他のデザイン値

## 開発環境セットアップ

リポジトリがクローン済みの状態で開発環境を構築する手順です。

### 1. Node.js のインストール

Node.jsをインストールしてください（バージョン要件は未確認）。

- [Node.js公式サイト](https://nodejs.org/) からダウンロード
- または、[nvm](https://github.com/nvm-sh/nvm) を使用してインストール

### 2. 依存関係のインストール

```bash
npm install
```

### 3. 開発サーバーの起動

```bash
npm run dev
```

Vite dev serverが起動し、`localhost:5173` でアクセス可能になります。

### 4. WordPressでの確認

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

