#!/usr/bin/env bash
set -e

echo "▶ 固定ページをインポートします"

MODE="${MODE:-upsert}"
DRY_RUN="${DRY_RUN:-0}"
JSON_PATH="${JSON_PATH:-wp-content/themes/t2025-12-24vite-wp/tools/pages.json}"

wp eval-file wp-content/themes/t2025-12-24vite-wp/tools/import-pages.php "${JSON_PATH}" "${MODE}" "${DRY_RUN}"

echo "✔ 完了"

