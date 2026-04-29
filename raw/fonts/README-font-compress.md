# フォント圧縮ツール

フォントファイルをwoff2形式に圧縮するためのスクリプトです。

> このドキュメントは **このテンプレート内の運用手順（テンプレ固有）** を扱います。  
> 導線の索引はナレッジベース `wiki/asset-compression-notes.md`（`/Users/yoshiaki/working/2026-04-23kn/wiki/asset-compression-notes.md`）を参照してください。**手順の詳細の正本は本ファイルおよび同ディレクトリの `font-compress.sh` / `font-compress-subset.sh`** とします。

## 初回セットアップ（1回だけ実行）

### 前提条件

- Python 3.x
- `fonttools` パッケージがインストールされていること

### fonttoolsのインストール（初回のみ）

```bash
pip install fonttools[woff]
```

## 使用方法

### 1. 全グリフを圧縮する場合

すべてのグリフを含むフォントファイルを圧縮します。

```bash
bash raw/fonts/font-compress.sh 入力フォントファイル.ttf 出力ファイル名.woff2
```

**使用するフォントファイルに合わせてフィル名を変更して実行:**
```bash
bash raw/fonts/font-compress.sh ./raw/fonts/NotoSansJP-VariableFont_wght.ttf ./src/assets/fonts/NotoSansJP-VariableFont_wght.woff2
```

### 2. サブセット化（特定の文字のみ）する場合

使用する文字列を指定して、必要なグリフのみを含むフォントファイルを生成します。ファイルサイズを削減できます。

```bash
bash raw/fonts/font-compress-subset.sh 入力フォントファイル.ttf 出力ファイル名.woff2 "使用する文字列"
```

**使用するフォントファイルに合わせてフィル名を変更して実行:**
```bash
bash raw/fonts/font-compress-subset.sh ./raw/fonts/Inter-VariableFont_opsz,wght.ttf ./src/assets/fonts/Inter-Subset.woff2 "Q&A"
```

## 出力ファイルの配置

圧縮したフォントファイルは `src/assets/fonts/` に配置してください。

## プロジェクト側への反映（必要に応じて追記・修正）

フォント圧縮の出力を `src/assets/fonts/` に配置したら、フォント名・ファイル名が一致するようにプロジェクト側の指定を合わせてください。

このテンプレート内の `NotoSansJP...` はあくまでサンプルです（あなたの実フォントに置き換え前提）。

### 静的テンプレ（Vite + EJS）の場合

- `src/assets/sass/base/_root.scss`
  - `@font-face` の `font-family` / `src: url("../fonts/...")` を、実際に配置した `*.woff2` に合わせて変更
  - `font-weight` 範囲も、必要なら合わせて調整
- `src/ejs/common/_head.ejs`
  - `<link rel="preload" href="/assets/fonts/...">` の `href` を、実際に配置した `*.woff2` に合わせて変更

### WordPress テンプレの場合

- `src/assets/sass/base/_root.scss`（上記と同様）
- `header.php`
  - `<link rel="preload" ...>` の `href` に **`<?php echo ty_vite_asset_url('src/assets/fonts/実ファイル名.woff2'); ?>`** を使う（開発／本番で URL が一致する。`functions-lib/func-vite.php` のコメント例参照）

（記述例・静的）
```scss
/* src/assets/sass/base/_root.scss（サンプル） */
@font-face {
  font-family: "Noto Sans JP VF";
  src: url("../fonts/NotoSansJP-VariableFont_wght.woff2") format("woff2");
  font-weight: 100 900;
  font-style: normal;
  font-display: swap;
}
```

```html
<!-- src/ejs/common/_head.ejs（サンプル・静的） -->
<link rel="preload" href="/assets/fonts/NotoSansJP-VariableFont_wght.woff2" as="font" type="font/woff2" crossorigin>
```

（記述例・WordPress `header.php` の preload）
```php
<link rel="preload" href="<?php echo ty_vite_asset_url('src/assets/fonts/NotoSansJP-VF.woff2'); ?>" as="font" type="font/woff2" crossorigin>
```

（例）実フォントを `MyFont-VF.woff2` にする場合は、`@font-face` の `url` と preload のファイル名をそれぞれ揃えて置き換えてください。

## 注意事項

- サブセット化する場合、指定した文字列に含まれる文字のみがフォントに含まれます
- 日本語フォントの場合、使用する文字を網羅的に指定する必要があります
- 可変フォント（Variable Font）にも対応しています
