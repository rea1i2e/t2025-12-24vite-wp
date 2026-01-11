#!/bin/bash
# プロジェクトID一括置換スクリプト
#
# 使い方:
#   ./scripts/replace-theme-id.sh 案件id
#
# 例:
#   ./scripts/replace-theme-id.sh t2025-01-15project-name

set -e

if [ -z "$1" ]; then
  echo "エラー: 新しいテーマID（案件id）を指定してください"
  echo "使い方: $0 案件id"
  echo "例: $0 t2025-01-15project-name"
  exit 1
fi

OLD_ID="t2025-12-24vite-wp"
NEW_ID="$1"

echo "プロジェクトID一括置換を実行します"
echo "置換: ${OLD_ID} → ${NEW_ID}"
echo ""

# 置換対象ファイル（MDファイルは除外）
files=(
  "package.json"
  "package-lock.json"
  "style.css"
  "tools/import-pages.php"
  "tools/import-pages.sh"
  "docs/deploy.md"
  "env.deploy.example"
)

# 置換実行
replaced_count=0
for file in "${files[@]}"; do
  if [ -f "$file" ]; then
    # macOS用のsed（-i '' が必要）
    if [[ "$OSTYPE" == "darwin"* ]]; then
      sed -i '' "s/${OLD_ID}/${NEW_ID}/g" "$file"
    else
      sed -i "s/${OLD_ID}/${NEW_ID}/g" "$file"
    fi
    echo "✓ $file"
    replaced_count=$((replaced_count + 1))
  else
    echo "⚠ $file が見つかりません（スキップ）"
  fi
done

echo ""
echo "完了: ${replaced_count} ファイルを置換しました"
echo ""
echo "注意: MDファイル（README.md、docs/*.md、tools/README.md）は置換していません"
echo "      これらはテンプレートとして ${OLD_ID} のまま残しています"

