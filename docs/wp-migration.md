# WordPress移行メモ（LOCAL / クラシックテーマ / Vite HMR + enqueue）

## 方針（決定事項）
- 開発: **WordPressで表示**しつつ、フロント資産は **Vite dev server(HMR)** を参照する
- 本番: **Vite build成果物（manifest）** から `wp_enqueue_script/style` で読み込む
- 画像: **WordPressネイティブ（サイズ生成・srcset等）を優先**し、静的HTML前提の後処理（after-buildによる`<picture>`化・`width/height`自動付与）は廃止/最小化する

## 仕組みの整理（3分類）

### 引き継げない（削除対象）
- EJS→HTML生成の仕組み
  - `vite-plugin-ejs` / `ViteEjsPlugin()`（`vite.config.js`）
  - EJSテンプレ群（`src/ejs/**`）
- 複数HTMLをエントリにして`dist/**/*.html`を作る仕組み
  - 静的ページ実体（`src/**/*.html`）
  - `globSync("src/**/*.html")` を `rollupOptions.input` に登録（`vite.config.js`）
- HTML後処理（静的HTML前提）
  - `scripts/after-build.mjs`（`dist/**/*.html`を書き換える設計のため、WPテンプレート運用と噛み合わない）
- `dist/` を対象にしたHTML検証
  - `.htmlvalidate.json`
  - `package.json` の `validate:html`（`html-validate dist/`）

### 引き継ぐには修正が必要
- Vite設定: HTMLエントリを廃止し、**JS/CSSエントリ + manifest** へ変更（WP enqueue前提）
- エントリポイント: テンプレ直書き（`/assets/sass/style.scss` 等）をやめ、Viteのエントリからまとめてimport
- ページ設定: `config/site.config.js` の `pages` 管理はWPの「固定ページ/メニュー/SEO」へ置換
- 画像最適化: テーマ同梱画像の最適化が不要なら、Viteのimagemin関連依存を削除して軽量化
- Sass内のパスが静的サイト前提（要修正）
  - `src/assets/sass/base/_root.scss`: `url("/fonts/...")`（サイトルート前提）
  - `src/assets/sass/components/*`: `url(/assets/images/...)` / `mask: url(/assets/images/...)`（サイトルート前提）

### そのまま使える（基本維持）
- SCSS構成（`src/assets/sass/**`）とPostCSS設定（`postcss.config.cjs`）
- JSモジュール群（`src/assets/js/**`）
- 依存ライブラリ（`flatpickr`, `@splidejs/*`, `kiso.css` 等）
- テーマ同梱の画像・フォント（ただし参照パスはWP基準に修正）

## 課題リスト（チェックリスト）
- [x] docs: このファイルに「作業ログ」を残す運用を開始
- [x] 不要物削除: EJS/静的HTML/after-build/静的サイト固有設定を削除
- [x] WP最小テーマ: `style.css` / `functions.php` / `index.php` / `header.php` / `footer.php` を追加
- [x] Vite連携(dev): `@vite/client` + エントリをWPから読み込めるようにする（HMR/フルリロード含む）
- [x] Vite連携(prod): `dist/.vite/manifest.json` を読んでenqueueする（JS由来CSSも含む）
- [x] 参照パス調整: フォント/画像/CSS内URLなどをWP基準に（Viteで解決できる形に整理）
- [x] 依存整理: 不要になったnpm依存（EJS/after-build/HTML検証等）を削除
- [x] ドロワーメニューが機能しない（開閉しない/反応しない）

## 課題の整理（優先度と分類）
### P0（体験/動作を止める）
- [x] メニューの供給元を確定（旧環境同様: `get_nav_items()` を単一の供給元とする）

### P1（移植/運用の中核）
- [ ] 既存phpファイルの引き継ぎ（**functions-lib** の取捨選択と統合）
  - すでに移植済み: 旧テンプレ互換のヘルパー（`functions-lib/func-legacy.php`）、テンプレ（`front-page.php` など）、components（`components/*`）
  - 未移植: CPT/ショートコード/セキュリティ/recaptcha 等（旧テーマ: `functions-lib/*.php`）
- [x] 画像の `width` / `height` 属性（テーマ同梱画像は `t2025_img()` で付与。WPメディアは `wp_get_attachment_image()` を優先）
- [x] 画像の `loading` 属性（default: `lazy`。LCP候補は `eager` + `fetchpriority="high"` を使用）
- [ ] 自動デプロイ
  - [ ] GitHub Actionsの設定（できればコマンドで再現できる形）
  - [x] `dist/` をコミットするかどうか方針決め（CIで `npm run build` → FTP転送するため、`dist/` はコミットしない）

### P2（整理・品質）
- [ ] Huskyを削除（依存・scripts・フック運用）
- [ ] 旧静的サイト資産の残骸を整理（`src/ejs/**` など、使わないものを最終削除）
- [ ] デバッグコード/スタイルの撤去（例: `src/assets/sass/components/_p-drawer.scss` の `background-color: pink;`）
- [ ] WebP生成ポリシーを案件ごとに決める（有効/無効、品質、サイズ比較の扱い）

### ドロワーメニュー不具合（原因候補）※解消済み
- JS側は `#js-menu` / `#js-drawer` / `#js-drawer-menu` が揃わないと初期化を中止します（`src/assets/js/_drawer.js` の `if (!menuButton || !drawer || !drawerMenu) return;`）。
- 現在のWPテンプレは **上記IDを固定で出力**しているため、次は以下を確認する:
  - クリックイベントがバインドされているか（`#js-menu`）
  - `aria-hidden` / `aria-expanded` の切り替えが期待通りか（CSSは `aria-hidden="false"` で表示）
  - `inert` の有無・ブラウザ互換（必要ならpolyfill/撤去）
  - `z-index` / `position` 競合（ヘッダー固定化・管理バー対応と干渉していないか）

## 作業ログ（解消方法の記録）
### YYYY-MM-DD
- 変更:
- 目的:
- 方法:
- 影響範囲:

### 2025-12-24
- 変更: `scripts/after-build.mjs` を削除、`.htmlvalidate.json` を削除、`package.json` から `validate:*` と after-build 実行を撤去
- 目的: WP運用では `dist/**/*.html` 前提の後処理/検証が成立しないため（画像はWPネイティブ優先）
- 方法: ファイル削除 + npm scripts整理
- 影響範囲: `npm run build` の挙動（HTML後処理なし）、HTML検証コマンド廃止

### 2025-12-24
- 変更: クラシックテーマ最小ファイル追加（`style.css`, `functions.php`, `header.php`, `footer.php`, `index.php`）
- 目的: WPで表示できる最小テーマ骨格を用意
- 方法: `wp_head()`/`wp_footer()`/`the_content()` の最小構成
- 影響範囲: テーマとして有効化可能

### 2025-12-24
- 変更: Vite連携の土台追加（`functions-lib/func-vite.php`, `functions.php`でenqueue、`vite.config.js`をJS/CSSエントリ+manifestへ移行）
- 目的: 開発（HMR）/本番（manifest）で同一エントリを読み込むため
- 方法: devは`@vite/client`とエントリを読み込み、prodは`dist/manifest.json`からCSS/JSをenqueue
- 影響範囲: `vite`のビルド対象がHTMLからJSエントリへ変更

### 2025-12-24
- 変更: Sass内の絶対パス参照を相対パスに変更（`/fonts`・`/assets/images`）
- 目的: WPではサイトルート直下に`/assets`や`/fonts`が存在する前提にできないため（Viteビルドで解決させる）
- 方法: `src/assets/sass/**` の `url(...)` / `mask: url(...)` を `src/` 配下への相対参照へ変更
- 影響範囲: CSSから参照するフォント/画像の解決方法

### 2025-12-24
- 変更: EJSテンプレ群と静的HTMLを削除（`src/ejs/**`, `src/**/*.html`）、静的サイト用設定を削除（`config/site.config.js`, `config/utils.js`）
- 目的: WPテーマ運用では不要・混乱要因になるため
- 方法: ファイル削除
- 影響範囲: 静的サイトとしてのビルド/プレビューは不可（WPテーマとして運用）

### 2025-12-24
- 変更: dev server判定とenqueueを調整（HTTPS環境を考慮、dev時はエントリをheadで読み込み）
- 目的: HTTPSのWP表示でMixed Contentを避けつつ、dev時のCSS適用遅れ（FOUC）を抑える
- 方法: `T2025_VITE_DEV_SERVER`をHTTPS優先で定義、dev判定の`wp_remote_head`で`sslverify=false`、dev時のエントリscriptをheadで読み込み
- 影響範囲: dev時の読み込み順、Vite dev serverのURL指定

### 2025-12-24
- 変更: CSSをJS importではなく「CSSエントリ」として読み込む方式へ変更
- 目的: dev時にCSSを`<link>`で先に適用しやすくするため
- 方法: Viteのrollup inputに `style.scss` を追加し、WP側はdev/prodで`style.scss`をenqueue、JSは`main.js`のみenqueue
- 影響範囲: manifestのエントリ、WP enqueueの関数名

### 2025-12-24
- 変更: `entrypoints/` を廃止し、Vite/WPのエントリを `src/assets/js/main.js` と `src/assets/sass/style.scss` に統一
- 目的: 入口ファイル（entrypoints）を挟まず、最小構成で運用するため
- 方法: `vite.config.js` の `rollupOptions.input` と `functions.php` のenqueue指定を差し替え、不要な `src/entrypoints/*` を削除
- 影響範囲: manifestのキー、WP enqueue指定

### 2025-12-24
- 変更: Sass内の相対パスを `style.scss` 基準に修正、フォントを `src/assets/fonts` に移動
- 目的: `vite build` で画像/フォントの参照が解決できず runtime に残ってしまう問題を避けるため
- 方法: `url("../../images/...")` → `url("../images/...")`、フォントは `src/public/fonts` → `src/assets/fonts` へ移動し `url("../fonts/...")` に統一
- 影響範囲: `background-image` / `mask` / `@font-face` の参照先

### 2025-12-24
- 変更: JS entryに紐づくCSS（例: ライブラリCSS）もprodでenqueue
- 目的: `src/assets/js/main.js` がimportしているCSSが本番で反映されない状態を解消
- 方法: `dist/.vite/manifest.json` の `css` 配列を読み、`wp_enqueue_style` で追加読み込み
- 影響範囲: 本番のCSS読み込み本数（`main-*.css` が追加される）

### 2025-12-24
- 変更: 旧テーマ（`t_2025-01-11wp`）のPHP資産を一部移植（テンプレ/コンポーネント/ヘルパー）
- 目的: 既存のPHPテンプレ資産を新テーマで動かしながら移行できるようにするため
- 方法:
  - `functions-lib/func-legacy.php` を追加し、旧テンプレ互換の関数（`page_path`, `img_path`, `get_nav_items`, `display_thumbnail` 等）を提供
  - `header.php` / `footer.php` を旧テーマのクラス・構造に寄せて更新
  - `front-page.php` / `page.php` / `single.php` / `archive.php` / `home.php` / `page-contact.php` / `404.php` を追加
  - 不足していた `components/p-pagenavi.php` は簡易版を新規作成
- 影響範囲:
  - テンプレ互換のため関数名は旧テーマ準拠（将来的に整理する余地あり）
  - 画像URLは `t2025_theme_asset_url()` 経由で dev/prod を吸収

## 開発（HMR）確認手順（HTTPSのWP）
### 前提
- WP表示URLがHTTPSの場合、Vite dev serverもHTTPSで配信しないとMixed Contentでブロックされる

### 手順（最小）
- テーマ直下に `.certs/` を作り、以下を配置
  - `localhost.pem`
  - `localhost-key.pem`
- `npm run dev`
- WPページのソースで以下を確認
  - `@vite/client` が読み込まれている
  - CSSが `https://localhost:5173/src/assets/sass/style.scss` から読み込まれている
  - JSが `https://localhost:5173/src/assets/js/main.js` から読み込まれている

## PHP変更の即時反映（dev時）
- PHPはHMRで差し替えできないため、**PHPファイル変更を検知してブラウザをフルリロード**する
- `vite.config.js` にPHP変更で `full-reload` を投げる処理を追加済み

## WebP生成の制御（案件ごと）
`vite.config.js` のWebP生成は環境変数で制御します。

- `VITE_ENABLE_WEBP`（default: true）: `true/false` でWebP生成をON/OFF
- `VITE_WEBP_QUALITY`（default: 75）: WebPのquality
- `VITE_WEBP_SKIP_IF_LARGER`（default: true）: `true` の場合は「最適化後より大きいWebPは出力しない」

### 2025-12-24
- 変更: prod側のmanifest参照パスを `dist/.vite/manifest.json` に追従（`functions-lib/func-vite.php`）
- 目的: `vite build` の実際の出力先に合わせて、WPが正しくhashファイルをenqueueできるようにするため
- 方法: `t2025_vite_manifest_path()` を `dist/.vite/manifest.json` 優先 + `dist/manifest.json` フォールバックに変更
- 影響範囲: 本番（build成果物）の読み込み

## 削除予定リスト（確定したらチェック）
### 静的HTML（WP移行後は不要）
- `src/index.html`
- `src/contact/index.html`
- `src/contact/thanks.html`
- `src/privacy/index.html`
- `src/demo/index.html`
- `src/demo/demo-accordion/index.html`
- `src/demo/demo-css-animation/index.html`
- `src/demo/demo-dialog/index.html`
- `src/demo/demo-fadein/index.html`
- `src/demo/demo-grid-layout/index.html`
- `src/demo/demo-hover-button/index.html`
- `src/demo/demo-hover-card/index.html`
- `src/demo/demo-hover-change/index.html`
- `src/demo/demo-hover-text/index.html`
- `src/demo/demo-splide/index.html`

### EJS
- `src/ejs/common/_head.ejs`
- `src/ejs/common/_header.ejs`
- `src/ejs/common/_footer.ejs`
- `src/ejs/components/*`（全14ファイル）
- `src/ejs/data/*`

### build後処理（静的HTML前提）
- `scripts/after-build.mjs`

### 静的サイト用のページ設定
- `config/site.config.js`
- `config/utils.js`


