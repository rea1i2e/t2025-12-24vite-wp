# フォント圧縮ツール

フォントファイルをwoff2形式に圧縮するためのスクリプトです。

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
bash scripts/font-compress.sh 入力フォントファイル.ttf 出力ファイル名.woff2
```

**例:**
```bash
bash scripts/font-compress.sh SourceSans3-VariableFont_wght.ttf SourceSans3-VariableFont_wght.woff2
```

### 2. サブセット化（特定の文字のみ）する場合

使用する文字列を指定して、必要なグリフのみを含むフォントファイルを生成します。ファイルサイズを削減できます。

```bash
bash scripts/font-compress-subset.sh 入力フォントファイル.ttf 出力ファイル名.woff2 "使用する文字列"
```

**例:**
```bash
bash scripts/font-compress-subset.sh Inter-VariableFont_opsz,wght.ttf Inter-Subset.woff2 "Q&A"
```

## 出力ファイルの配置

圧縮したフォントファイルは `src/assets/fonts/` に配置してください。

## 注意事項

- サブセット化する場合、指定した文字列に含まれる文字のみがフォントに含まれます
- 日本語フォントの場合、使用する文字を網羅的に指定する必要があります
- 可変フォント（Variable Font）にも対応しています
