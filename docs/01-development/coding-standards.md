# コーディング規約

汎用的なコーディングルール（HTML/CSS/Sass/JS/PHP 共通）は、第二の脳（`/Users/yoshiaki/working/2026-04-23kn/`）の `wiki/coding-conventions.md` および子ページ（`wiki/coding-*.md`）を参照してください。旧 `2026-03-20kn` の `coding-rules/` は廃止とする。

このドキュメントには、**このテンプレート固有のルール**のみを記載します。

---

## PHP（テンプレート固有）

### 関数名プレフィックス

- **テーマ内で定義するPHP関数には必ず `ty_` プレフィックスを付与する** — WordPressやプラグインの関数名との衝突を避けるため。
  - 例: `ty_theme_image_url()`, `ty_register_post_type_works()`

### `function_exists` の使用ルール

- **プラグインで定義されている関数を使うときのみ `function_exists` を使用する**
- **子テーマを想定しないため、重複防止のための `function_exists` は不要**
- **同一テーマ内の関数を参照する場合も `function_exists` チェックは不要**（ファイル読み込み順序で解決）

### ファイル読み込み順序

- **`functions.php` の `$ordered`**: 依存関係があるファイルのみ追加する（通常は空でOK）
  - トップレベルで別ファイルの関数/定数を参照する場合は順序指定が必要

### コーディングスタイル

- **型宣言を付与する**（`declare(strict_types=1);` を使用）

---

## CSS/Sass（テンプレート固有）

### `kiso.css` を前提とした記述

以下は `kiso.css`（npm）・`_reset.scss`・`_base.scss` で適用済みのため、個別SCSSで重複して書かない。

#### kiso.css で適用済み

| プロパティ | 対象 |
|---|---|
| `box-sizing: border-box` | `*, ::before, ::after` |
| `margin: unset` | `body` |
| `margin-block: unset` | `h2〜h6`, `p`, `blockquote`, `figure`, `pre`, `ul`, `ol`, `dl`, `menu` |
| `padding-inline-start: unset` | `ul`, `ol`, `menu` |
| `list-style-type: ""` | `ul`, `ol`, `menu` |
| `color: unset` / `text-decoration-line: unset` | `a:any-link` |
| `vertical-align: bottom` / `max-inline-size: 100%` / `block-size: auto` | `img`, `video`, `iframe` など埋め込み要素 |
| `border-collapse: collapse` | `table` |
| `border: unset` / `font: unset` / `color: unset` など | `button`, `input`, `select`, `textarea` |
| `cursor: pointer` | クリッカブル要素 |
| `line-height: 1.5` | `:root` |
| `font-family: sans-serif` | `:root` |
| `overflow-wrap: anywhere` | `:root` |
| `background-color: unset` | `button`, `input[type="button"]` など |
| `touch-action: manipulation` | `button`, `[role="button"]` など |

#### `_reset.scss` で追加適用済み

| プロパティ | 対象 |
|---|---|
| `display: block; width: 100%` | `img` |
| `display: block; max-width: 100%; height: auto` | `iframe` |
| `border-width: 0; padding: 0` | `button` |
| `margin-block: unset` | `h1` |

#### `_base.scss` で適用済み

| プロパティ | 対象 |
|---|---|
| `container-type: inline-size`, `display: grid`, `min-height: 100vh`, `line-height: 1.5`, `font-family: var(--base-font-family)` | `body` |
| `transition: opacity var(--hover-transition)` | `a` |
| `opacity: 0.7` on hover / focus-visible | `a` |
| `pointer-events: none`（PC時） | `a[href^="tel:"]` |
| `aspect-ratio: 16 / 9` | `iframe[src*="youtube.com"]` |

### プロパティの指定順

CSSプロパティの指定順は、メディアクエリの有無を優先し、その中でカテゴリ別の順序で記述する：

1. メディアクエリなしのスタイル（基本スタイル）を先に記述
2. メディアクエリありのスタイル（`@include mq()`）を後に記述
3. カテゴリ別の順序:
   1. レイアウト関連: `display`、`position`、`z-index`、`flex-direction`、`align-items`、`gap`、`margin` など
   2. サイズ・スペーシング: `width`、`height`、`min-height`、`max-width`、`padding` など
   3. 見た目（背景・ボーダー）: `background-color`、`border`、`border-radius`、`box-shadow`、`filter` など
   4. テキスト・フォント: `font-size`、`font-weight`、`color`、`text-align`、`line-height` など
   5. その他: `cursor`、`transition`、`opacity` など

---

## 画像の扱い（テンプレート固有）

### CSS内の画像

- `src/assets/images/**` を Sass の `url(...)` 経由で参照する（Viteがビルド時に解決）

### HTML内の画像

- `ty_theme_image_url()` を使用して画像URLを取得する
- WordPressのメディアライブラリを使用する場合は `wp_get_attachment_image()` を優先する

---

## 参考資料

- **設計判断**: [architecture.md](architecture.md) を参照
- **開発ガイド**: [development.md](development.md) を参照
- **汎用コーディングルール**: ナレッジリポジトリ `coding-rules/` を参照
