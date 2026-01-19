# コーディング規約

このドキュメントでは、プロジェクトで守るべきコーディング規約を説明します。

## PHP

### 関数名

- **プレフィックス**: テーマ内で定義するPHP関数には必ず `ty_` プレフィックスを付与してください
  - 理由: WordPressやプラグインの関数名との衝突を避けるため
  - 例: `ty_theme_image_url()`, `ty_register_post_type_works()`

### function_exists の使用ルール

- **プラグイン関数のチェック**: プラグインで定義されている関数を使うときのみ `function_exists` を使用してください
- **子テーマでの上書きを想定しない**: 子テーマを想定しないため、関数定義の重複防止のための `function_exists` は不要です
- **同一テーマ内の関数への依存**: 同じテーマ内の関数を参照する場合、`function_exists` チェックは不要です（ファイル読み込み順序で解決）

### ファイル読み込み順序

- **`functions.php` の `$ordered`**: 依存関係があるファイルのみ `$ordered` 配列に追加することを推奨します
  - 通常は空でOKですが、トップレベルで別ファイルの関数/定数を参照する場合は順序指定が必要です

### コーディングスタイル

- **型宣言**: PHP関数には型宣言を付与することを推奨します（`declare(strict_types=1);` を使用）
- **コメント**: 関数の目的や引数の説明をコメントで記載することを推奨します

### 配列の記述

- **配列リテラル**: `array()` ではなく `[]` を使用してください

### データ取得とHTMLの記述

- **分離**: データ取得とHTMLの記述は可能な限り分けてください

## CSS/Sass

### marginの使用

原則として `margin-block-start` のみを使用します。

- `margin-block-end` や `margin-block` は使用しない
- `margin-inline-start: 0;` はリセットCSSで既に指定されているため、明示的に書く必要はない
- 要素間の間隔は次の要素の `margin-block-start` で制御する
- 例: `.element { margin-block-start: rem(20); }`

### コンポーネントの外側余白

コンポーネント自体には外側の余白（margin）をつけません。

- コンポーネントは再利用可能な部品のため、外側の余白は親要素で制御する
- 使用する側で親要素を追加し、そこに余白をつける
- 例:
  ```scss
  // コンポーネント（components-demo/_p-post-nav.scss）
  .p-post-nav {
    display: flex;
    // marginはつけない
  }
  
  // 使用する側（components/_p-single.scss）
  .p-single__post-nav {
    margin-block-start: rem(60);
    @include mq() {
      margin-block-start: rem(80);
    }
  }
  ```

### 入れ子（ネスト）の使い方

基本的にフラットな構造を維持します。

- **BEMのクラス名は入れ子にしない**: `.p-component__element` は全てトップレベルで記述
- **子要素（タグ名）もフラットに記述**: `.p-component__element img` のようにスペース区切りで記述（入れ子にしない）
- **メディアクエリは入れ子で記述**: `@include mq() { ... }` は入れ子で使用
- **複数のセレクタはカンマで区切る**: `.element1, .element2 { ... }` のようにフラットに記述
- **複雑なセレクタもフラットに記述**: `.parent:has(.child) .target` のようにスペース区切りで記述
- 例（正）: 
  ```scss
  .p-single__thumbnail {
    margin-block-start: rem(20);
    @include mq() {
      margin-block-start: rem(30);
    }
  }
  .p-single__thumbnail img {
    width: 100%;
  }
  ```
- 例（誤）:
  ```scss
  .p-single__thumbnail {
    margin-block-start: rem(20);
    img {
      width: 100%;
    }
  }
  ```

## JavaScript

### モジュール構成

- **エントリファイル**: `src/assets/js/main.js` がエントリファイルです
- **機能別モジュール**: 機能別にモジュールを分割して、`main.js` でインポートします

### モジュール命名規則

機能別モジュールは `_` プレフィックスを付与：

- `_header.js`: ヘッダー関連
- `_drawer.js`: ドロワーメニュー
- `_modal.js`: モーダル
- `_accordion.js`: アコーディオン

## 画像の扱い

### CSS内の画像

- `src/assets/images/**` を Sass の `url(...)` 経由で参照することを推奨します
- Viteがビルド時に解決し、ハッシュ付きファイル名に変換されます

### HTML内の画像

- `ty_theme_image_url()` を使用して画像URLを取得することを推奨します
- WordPressのメディアライブラリを使用する場合は `wp_get_attachment_image()` を優先することを推奨します

## スクリプトの実行方法

実行権限を付与せず、`bash`コマンドで実行する方法を推奨します。

- 理由: クロスプラットフォーム対応、Git管理の簡素化、初回セットアップの削減
- 例: `bash scripts/font-compress.sh input.ttf output.woff2`
- 各スクリプトのREADMEでは`bash`コマンドのみを記載し、実行権限付与の方法は記載しない

## 参考資料

- **設計判断**: [architecture.md](architecture.md) を参照
- **開発ガイド**: [development.md](development.md) を参照
