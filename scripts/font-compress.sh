#!/bin/bash
# フォント圧縮コマンド（全グリフ）
# 使用方法: ./scripts/font-compress.sh 入力フォントファイル名.ttf 出力ファイル名.woff2

if [ $# -lt 2 ]; then
  echo "使用方法: $0 <入力フォントファイル> <出力ファイル名>"
  echo "例: $0 SourceSans3-VariableFont_wght.ttf SourceSans3-VariableFont_wght.woff2"
  exit 1
fi

INPUT_FILE="$1"
OUTPUT_FILE="$2"

if [ ! -f "$INPUT_FILE" ]; then
  echo "エラー: 入力ファイル '$INPUT_FILE' が見つかりません"
  exit 1
fi

pyftsubset "$INPUT_FILE" \
  --output-file="$OUTPUT_FILE" \
  --flavor=woff2 \
  --layout-features='*' \
  --glyphs='*'

if [ $? -eq 0 ]; then
  echo "圧縮完了: $OUTPUT_FILE"
else
  echo "エラー: フォント圧縮に失敗しました"
  exit 1
fi
