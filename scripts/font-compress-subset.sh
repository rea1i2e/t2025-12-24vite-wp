#!/bin/bash
# フォント圧縮コマンド（サブセット化）
# 使用方法: ./scripts/font-compress-subset.sh 入力フォントファイル名.ttf 出力ファイル名.woff2 "使用する文字列"

if [ $# -lt 3 ]; then
  echo "使用方法: $0 <入力フォントファイル> <出力ファイル名> <使用する文字列>"
  echo "例: $0 Inter-VariableFont_opsz,wght.ttf Inter-Subset.woff2 \"Q&A\""
  exit 1
fi

INPUT_FILE="$1"
OUTPUT_FILE="$2"
TEXT="$3"

if [ ! -f "$INPUT_FILE" ]; then
  echo "エラー: 入力ファイル '$INPUT_FILE' が見つかりません"
  exit 1
fi

pyftsubset "$INPUT_FILE" \
  --output-file="$OUTPUT_FILE" \
  --flavor=woff2 \
  --layout-features='*' \
  --text="$TEXT"

if [ $? -eq 0 ]; then
  echo "圧縮完了: $OUTPUT_FILE"
else
  echo "エラー: フォント圧縮に失敗しました"
  exit 1
fi
