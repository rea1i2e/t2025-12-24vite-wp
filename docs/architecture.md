# 構成調査レポート（t2025-12-24vite-wp / WordPress版）

対象リポジトリ: `t2025-12-24vite-wp`  
主な目的: WordPress（クラシックテーマ）用に作り替えた現在の構成と「どのファイルでどう実現しているか」を根拠付きで整理する

---

## 1. 概要

このプロジェクトは **WordPressクラシックテーマ + Vite + Sass** の制作環境です。

- **開発**: WordPressで表示しつつ、CSS/JSは **Vite dev server（HMR）** を参照  
- **本番**: `vite build` で生成された **`dist/.vite/manifest.json`** を参照し、WordPress側で `wp_enqueue_style/script` する

静的サイト前提だった以下は廃止済みです。
- **EJSテンプレ**（`src/ejs/**`）
- **静的HTMLエントリ**（`src/**/*.html`）
- **after-build（HTML後処理）**
- **html-validate**
- **husky（npm依存/script）**

---

## 2. 全体フロー（開発・本番）

### 開発環境（dev）のフロー

```
ブラウザ → WordPressページリクエスト
    ↓
WP_Theme_PHP（header.php, functions.php など）
    ↓
load_assets（アセット読み込み指示）
    ↓
Vite_dev_server(HMR) ← localhost:5173 から CSS/JS を取得
```

### 本番環境（prod）のフロー

```
npm run build
    ↓
vite build
    ↓
dist/ にハッシュ付きアセットを出力
    ├─ assets/js/main-*.js
    ├─ assets/css/style-*.css
    └─ .vite/manifest.json（パスマッピング）
    ↓
WP_Theme_PHP
    ↓
read_manifest（dist/.vite/manifest.json を読み込み）
    ↓
dist/ から実際のファイルを enqueue
```

ポイント:
- WordPressはPHPテンプレートでHTMLを生成するため、**HTMLをエントリにしない**。
- 代わりに、Viteのエントリは **JS/CSSファイル**（`src/assets/js/main.js`, `src/assets/sass/style.scss`）。

---

## 3. 機能一覧と実現方法（根拠ファイル付き）

### 3.1 WordPressテーマ（クラシック）

**実現箇所**
- `style.css`: テーマヘッダ
- `header.php`, `footer.php`, `index.php`: 最小テンプレ

補足:
- 現状 `wp_nav_menu()` は使用しない方針（空の`<ul>`を固定出力）。

### 3.2 Vite連携（dev/HMR と prod/manifest）

**実現箇所**
- `functions.php`
  - `functions-lib/` 配下のファイルを読み込むローダー
- `functions-lib/func-vite.php`
  - dev/prod判定とURL解決のヘルパー関数群
  - `TY_VITE_DEV_SERVER` を定義（`is_ssl()` に応じて `https://localhost:5173` または `http://localhost:5173`）
  - dev: `@vite/client` と `src/assets/**` を `localhost:5173` から読み込み
  - prod: `dist/.vite/manifest.json` を読んで `dist/assets/**` を読み込み
- `functions-lib/func-vite-assets.php`
  - enqueueの実装（`wp_enqueue_scripts` フックで実行）
  - `script_loader_tag` で `type="module"` を強制付与（devで `import` を扱うため）
  - JS entryに紐づく `css`（例: ライブラリCSS）も prodでenqueue

#### dev / prod の判定方法
判定は `functions-lib/func-vite.php` の `ty_vite_is_dev()` で行います。

- `functions-lib/func-vite.php` で `TY_VITE_DEV_SERVER` を定義（`is_ssl()` に応じて `https://localhost:5173` または `http://localhost:5173`）
- `TY_VITE_DEV_SERVER/@vite/client` に `wp_remote_head()` で到達確認（短いtimeout）
  - 到達できる: **dev扱い**（Vite dev serverから `@vite/client` と `src/assets/**` を読み込む）
  - 到達できない: **prod扱い**（`dist/.vite/manifest.json` を参照して `dist/assets/**` をenqueue）

### 3.3 SCSS → CSS（Vite経由）

**実現箇所**
- `src/assets/sass/style.scss`: Sassの集約
- `vite.config.js`: `rollupOptions.input` に `style.scss` を登録
- `postcss.config.cjs`: `autoprefixer`, `postcss-sort-media-queries`

**出力**
- `dist/assets/css/style-*.css`: 自作Sass

### 3.4 JSバンドル

**実現箇所**
- `src/assets/js/main.js`: 機能別モジュールをimportして束ねる
- `vite.config.js`: `rollupOptions.input` に `main.js` を登録

**出力**
- `dist/assets/js/main-*.js`
- `dist/assets/css/main-*.css`: JS側がimportするCSS（例: SplideのCSS）

### 3.5 画像・フォントの扱い

- 画像（背景画像等）: `src/assets/images/**` を Sass の `url(...)` 経由で参照し、Viteがビルド対象として解決
- フォント: `src/assets/fonts/**` を `@font-face` で参照し、ビルドで `dist/assets/*.woff2` に出力

補足（`<img>` の画像）:
- `<img>` はCSSの `url(...)` のように参照を辿れないため、`vite build` 時に `src/assets/images/**` を `dist/assets/images/**` へ出力し、`dist/theme-assets.json` を生成してPHPが解決する方式を採用

---

## 4. コーディング規約

### 4.1 関数の命名ルール

テーマ内で定義するPHP関数には、**`ty_` プレフィックス**を付与します。

**理由**
- WordPressやプラグインの関数名との衝突を避ける
- テーマ独自の関数であることを明示する

**例**
```php
function ty_theme_image_url(string $pathUnderImages): string { ... }
function ty_register_post_type_works(): void { ... }
function ty_custom_main_query_works($query): void { ... }
```


### 4.2 `function_exists` の使用ルール

`function_exists` は以下の場合のみ使用します。

#### 使用する場合

1. **プラグインで定義されている関数を使うとき**
   - プラグインが有効化されている場合のみ実行したい処理
   - 例: パンくずリストプラグインの関数がある場合だけ出力

```php
// プラグイン関数のチェック（必要）
if (function_exists('bcn_display') && !is_front_page()) {
    bcn_display();
}
```

#### 使用しない場合

1. **子テーマでの上書きを想定しない**
   - 子テーマを想定しないため、関数定義の重複防止のための `function_exists` は不要
   - 関数名の重複があれば、エラーが出るため気づくことができる

```php
// ❌ 不要（子テーマを想定しないため）
if (!function_exists('ty_register_post_type_works')) {
    function ty_register_post_type_works(): void { ... }
}

// ✅ 正しい（そのまま定義）
function ty_register_post_type_works(): void { ... }
```

2. **同じテーマ内の関数への依存チェック**
   - 関数内で同じテーマ内の関数を参照する場合、`function_exists` チェックは不要
   - リクエスト処理時点では全ファイルが読み込まれているため
   - ファイル読み込み順序の問題は `functions.php` の `$ordered` で解決

```php
// ❌ 不要（同じテーマ内の関数なので）
function ty_img(string $pathUnderImages, string $alt = '', array $attrs = []): string {
    $url = function_exists('ty_theme_image_url') ? ty_theme_image_url($pathUnderImages) : '';
    // ...
}

// ✅ 正しい（直接呼び出す）
function ty_img(string $pathUnderImages, string $alt = '', array $attrs = []): string {
    $url = ty_theme_image_url($pathUnderImages);
    // ...
}
```

3. **トップレベルでの依存チェック**
   - トップレベルで別ファイルの関数を参照する場合は、`function_exists` ではなく `functions.php` の `$ordered` で読み込み順序を指定

```php
// functions.php
$ordered = [
    'func-vite.php', // 先に読み込む
];

// func-images.php（トップレベルでの参照）
function ty_theme_asset_file_path(string $srcPath): string {
    // ❌ 不要
    // if (!function_exists('ty_vite_theme_assets_map')) return '';
    
    // ✅ 正しい（$ordered で先に読み込まれているため）
    $map = ty_vite_theme_assets_map();
    // ...
}
```

**まとめ**
- プラグイン関数のチェック → **使用する**
- 子テーマでの上書きを想定した保護 → **使用しない**
- 同じテーマ内の関数への依存チェック → **使用しない**（ファイル読み込み順序で解決）

---

## 5. ディレクトリ構成（要点）

- `header.php` / `footer.php` / `index.php` / `functions.php`: WPクラシックテーマ
- `functions-lib/func-vite.php`: Vite連携（dev/prod判定 + URL解決）
- `functions-lib/func-vite-assets.php`: アセット読み込み（enqueue実装）
- `src/assets/`
  - `sass/`: 自作Sass
  - `js/`: 自作JS
  - `images/`: テーマ同梱画像
  - `fonts/`: テーマ同梱フォント（Vite管理）
- `dist/`: `vite build` の成果物（hash付きassets + `.vite/manifest.json`）
- `docs/migration-log.md`: 移行メモ/課題/解消ログ

---

## 6. 変更・拡張ポイント（短く）

- **テンプレ追加**: `front-page.php`, `page.php`, `single.php` 等を追加してWPの表示導線を整備
- **メニュー実装**: `wp_nav_menu` を使わない前提で、ナビの中身をどう供給するか決める（静的/カスタム/ブロック等）
- **画像最適化**: `vite-plugin-imagemin` を残すか撤去するか（WPネイティブ運用との住み分け）

---

## 7. 付録（関連ファイル早見）

- Vite設定: `vite.config.js`
- Vite連携: `functions.php`, `functions-lib/func-vite.php`, `functions-lib/func-vite-assets.php`
- Sass入口: `src/assets/sass/style.scss`
- JS入口: `src/assets/js/main.js`
- 移行ログ: `docs/migration-log.md`
