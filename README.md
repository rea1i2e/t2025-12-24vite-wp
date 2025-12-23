# t2025-10-01vite

Vite + EJS + Sass 構成の静的サイトテンプレート。ビルド時に画像圧縮と WebP 生成、HTML の <img> を <picture> 最適化、width/height 自動付与を行います。（AVIFは「生成」は未対応ですが、`dist/` にAVIFが存在する場合は <picture> に <source type="image/avif"> を挿入します）

## GitHub CLIを使った導入手順

### 新規リポジトリ作成＋クローンする場合は、コマンドを実行
```bash
gh repo create 新規リポジトリ名 \
  --template rea1i2e/t2025-10-01vite \
  --private \
  --description "リポジトリの説明文" && \
sleep 5 && \
gh repo clone GitHubのユーザー名/新規リポジトリ名
```

## 必要要件

### システム要件
- **Node.js**: 18.x 以上（推奨: 20.x LTS）
- **npm**: 9.x 以上
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

### 環境確認方法
セットアップ前に、必要なツールがインストールされているか確認してください。

- **Node.js のバージョン確認**
  ```bash
  node --version
  ```
  18.x 以上であることを確認

- **npm のバージョン確認**
  ```bash
  npm --version
  ```
  9.x 以上であることを確認

- **プラットフォーム別確認**
  - **macOS**: Xcode Command Line Tools
    ```bash
    xcode-select -p
    ```
    `/Library/Developer/CommandLineTools` が表示されればOK

  - **Windows**: Visual Studio Build Tools（未検証）
    - コントロールパネル > プログラムと機能 で確認

  - **Linux**: build-essential
    ```bash
    dpkg -l | grep build-essential
    ```
    build-essential が表示されればOK

### セットアップ手順
1. Node.js 18.x以上をインストール
2. リポジトリをクローン
3. 依存関係をインストール
   ```bash
   npm install
   ```
4. 開発サーバーを起動
   ```bash
   npm run dev
   ```


## スクリプト
- 開発サーバー: EJS/HTML/Sass/JS を監視して自動反映
```bash
npm run dev
```

- 本番ビルド: `dist/` に出力 + ビルド後最適化（after-build）
※ ビルドに時webP生成、picture, sourceタグ挿入、width/height自動付与
```bash
npm run build
```

- ビルド確認（簡易サーバー）
※ビルド後のファイルをブラウザで確認
```bash
npm run preview
# もしくは
npm run build:preview
```

- 不要なファイル削除
```bash
npm run clean        # dist と一時生成を削除
npm run clean:all    # + node_modules と lock も削除
npm run reinstall    # クリーン後に再インストール
```

## ディレクトリ構成（主要）
```
src/                 # 開発用ルート（Vite root）
  index.html         # エントリ（複数HTML対応）
  contact/index.html
  demo/**/index.html
  privacy/index.html
  ejs/               # EJS 共通パーツ/部品/データ
    common/(_head.ejs, _header.ejs, _footer.ejs)
    components/（ページ部品）
    data/（ダミーデータ等）
  assets/
    sass/            # Sass（グロブインポート対応）
    js/              # JSモジュール
    images/          # 画像（dummy/common）
  public/            # Vite public（root=src のため src/public）
dist/                # 本番出力（ビルド生成物）
scripts/after-build.mjs  # HTML後処理スクリプト
vite.config.js
```

## 開発フロー
1. `npm run dev` を実行
2. `src/` 以下を編集（EJS/HTML/Sass/JS）
3. ビルドは `npm run build`、出力は `dist/`

## EJS の使い方
- 既定変数（`vite.config.js` 内）:
  - `siteName`: サイト名
- **サイト設定（`config/site.config.js`）**:
  - `siteName`: サイト名（タイトル生成に使用）
  - `domain`: ドメイン（OGP/canonical等に使用）
  - `titleSeparator`: タイトル区切り文字（デフォルト: " | "）
  - `headerExcludePages`: ヘッダーメニューから除外するページのキー配列
  - `pages`: ページ情報のオブジェクト（キー: ページ識別子、値: ページ情報）
    - `label`: メニュー表示名
    - `path`: ページパス（または外部URL）
    - `root`: そのページから見たルート相対（例: `"../"`）
    - `title`: ページタイトル（省略時はサイト名のみ）
    - `description`: メタディスクリプション
    - `keywords`: メタキーワード
    - `targetBlank`: 外部リンクで新しいタブで開く場合に`true`
- htmlファイル
  - `src/index.html` 等のHTMLから `ejs/common` や `ejs/components` を `include` して組み立てます。
  - ページごとの `pageData`（`pages['top']` など）から `page` を組み立て、`_head.ejs` へ渡して title/description 等を出力します。
## Sass/スタイル
- `vite-plugin-sass-glob-import` により、Sass のグロブインポートが可能です。
- `src/assets/sass/style.scss` をエントリに、`base/`, `components/`, `layouts/`, `utility/` へ分割。

## 画像最適化（ビルド時）
- プラグイン: `@vheemstra/vite-plugin-imagemin`
- 圧縮対象: PNG/JPEG/GIF/SVG
- 生成:
  - WebP: JPEG/PNG/GIF から生成
- 出力パス:（現状、images以下のディレクトリ構成はフラットになります）
  - 画像: `assets/images/[name]-[hash][ext]`
  - CSS: `assets/css/[name]-[hash].css`
  - JS: `assets/js/[name]-[hash].js`

## after-build（HTML 後処理）の挙動
- 対象: `dist/**/*.html`
- 処理内容:
  - `<img>` に `width`/`height` を自動付与
  - WebP/AVIF が `dist` に存在する場合、`<picture>` 化して `<source>` を自動挿入
  - 既存 `<picture>` がある場合は崩さず、不足する `<source>` のみ追加
  - `<source>` それぞれにも参照ファイルの実寸を付与
  - 最後に HTML を整形（`js-beautify`）
- スキップ条件:
  - 外部 URL / `data:` 画像
  - 非ラスタ（SVG を `<picture>` 化はしません）
- ログ:
  - 変換件数、寸法付与のみの件数を標準出力に表示

## ビルド入力（複数 HTML 対応）
- `globSync("src/**/*.html")` をエントリーとして登録
- `src/` 以下に HTML を追加すると、自動的にビルド対象に含まれます

## よくあるトラブル
- `sharp` のビルド/インストール失敗
  - macOS: Xcode Command Line Tools の導入を確認
  - Node のメジャー更新後は `npm rebuild sharp` を試す
- 権限エラー
  - `dist/` やプロジェクトルートの書き込み権限を確認
- 画像が `<picture>` 化されない
  - 対象拡張子か、対応 WebP/AVIF が `dist` に存在するか確認
  - `data:` や外部 URL は対象外

## ライセンス
プロジェクトに合わせて追記してください。

## サイト設定（site.config.js）

### 基本設定
```javascript
export const siteConfig = {
  siteName: "サイト名",
  domain: "https://example.com/",
  titleSeparator: " | ", // タイトル区切り文字
  headerExcludePages: ['privacy'], // ヘッダーから除外するページ
  pages: {/* ページオブジェクト */} // 配列ではなくオブジェクト形式
};
```

### ページ設定
各ページは以下のプロパティを持ちます：

- **必須プロパティ**:
  - `label`: ヘッダーメニューの表示名
  - `path`: ページパス（または外部URL）
  - `root`: そのページから見たルート相対

- **オプションプロパティ**:
  - `title`: ページタイトル（省略時はサイト名のみ）
  - `description`: メタディスクリプション
  - `keywords`: メタキーワード
  - `targetBlank`: `true`で新しいタブで開く（外部リンク用）

### 使用例
```javascript
pages: {
  contact: {
    label: "お問い合わせ",
    root: "../",
    path: "contact/",
    title: "お問い合わせ",
    description: "お問い合わせフォームページです。"
  },
  privacy: {
    label: "個人情報保護方針",
    root: "../",
    path: "privacy/",
    title: "個人情報保護方針",
    description: "個人情報保護方針ページです。"
  },
  x: {
    label: "X",
    root: "",
    path: "https://x.com/xxxxxxxx",
    targetBlank: true
  }
}
```

### ページへのアクセス方法
EJSファイル内では以下のようにページ情報にアクセスできます：

```ejs
<!-- 直接アクセス（推奨） -->
<a href="<%- page.root + pages.privacy.path %>">プライバシーポリシー</a>

<!-- ループ処理 -->
<% Object.entries(pages).forEach(([key, item]) => { %>
  <a href="<%- item.path.startsWith('http') ? item.path : page.root + item.path %>"><%- item.label %></a>
<% }); %>
```

### タイトル生成
- ページに`title`が設定されている場合: `ページタイトル | サイト名`
- ページに`title`が設定されていない場合: `サイト名`
- 区切り文字は`titleSeparator`で変更可能

## ページ情報へのアクセス方法

### 直接アクセス（推奨）
```ejs
<!-- 特定のページのURLを取得 -->
<a href="<%- page.root + pages.contact.path %>">お問い合わせ</a>

<!-- 特定のページのラベルを取得 -->
<h1><%- pages.contact.label %></h1>
```

### ループ処理
```ejs
<!-- すべてのページをループ処理 -->
<% Object.entries(pages).forEach(([key, item]) => { %>
  <a href="<%- item.path.startsWith('http') ? item.path : page.root + item.path %>" 
     <%= item.targetBlank ? 'target="_blank" rel="noopener noreferrer"' : '' %>>
    <%- item.label %>
  </a>
<% }); %>
```

### 条件分岐
```ejs
<!-- 特定のページが存在するかチェック -->
<% if (pages.privacy) { %>
  <a href="<%- page.root + pages.privacy.path %>">プライバシーポリシー</a>
<% } %>
```


