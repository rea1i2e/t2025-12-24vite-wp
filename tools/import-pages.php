<?php
/**
 * pages.json を読み込み、固定ページを一括作成/更新する
 *
 * 使い方例:
 * wp eval-file wp-content/themes/t2025-12-24vite-wp/tools/import-pages.php wp-content/themes/t2025-12-24vite-wp/tools/pages.json upsert
 * wp eval-file wp-content/themes/t2025-12-24vite-wp/tools/import-pages.php wp-content/themes/t2025-12-24vite-wp/tools/pages.json upsert 1
 *
 * 引数:
 * 1) json_path（必須）: JSONファイルのパス（例: wp-content/themes/t2025-12-24vite-wp/tools/pages.json）
 * 2) mode（任意）    : create|upsert|warn|skip（既定: upsert）
 * 3) dry_run（任意） : 1ならDB変更しない（既定: 0）
 *
 * 注意:
 * - `wp eval-file` は環境によって未知のオプション（例: --mode / --dry-run）を弾くことがあるため、
 *   フラグではなく「位置引数」で受け取る実装にしています。
 */

/**
 * JSONフォーマット想定:
 * [
 *   {"title":"About","slug":"about","parent_slug":"","status":"draft","content":""},
 *   {"title":"Message","slug":"message","parent_slug":"about","status":"draft","content":""}
 * ]
 * ※ `parent` キーも互換で受け付けます（`parent_slug` 優先）。
 */

if (php_sapi_name() !== 'cli') {
  exit("This script must be run via WP-CLI.\n");
}

if (!class_exists('WP_CLI')) {
  exit("WP_CLI not available.\n");
}

function get_eval_file_extra_args() {
  $argv = $_SERVER['argv'] ?? [];
  if (!is_array($argv) || !$argv) {
    return [];
  }

  $idx = array_search('eval-file', $argv, true);
  if ($idx === false) {
    return [];
  }

  $j = $idx + 1;
  // eval-file の次はファイルパスなのでスキップ
  if (isset($argv[$j])) {
    $j++;
  }

  // `--` が挟まれている場合はそれもスキップ
  if (isset($argv[$j]) && $argv[$j] === '--') {
    $j++;
  }

  return array_slice($argv, $j);
}

$extra_args = get_eval_file_extra_args();

$path = $extra_args[0] ?? null;
$mode = $extra_args[1] ?? 'upsert';
$dry_run_raw = $extra_args[2] ?? '0';
$dry_run = in_array(strtolower((string) $dry_run_raw), ['1', 'true', 'yes', 'on'], true);

if (!$path) {
  WP_CLI::error(
    "JSON path is required.
" .
    "Usage: wp eval-file wp-content/themes/t2025-12-24vite-wp/tools/import-pages.php <json_path> [mode] [dry_run]
" .
    "Example: wp eval-file wp-content/themes/t2025-12-24vite-wp/tools/import-pages.php wp-content/themes/t2025-12-24vite-wp/tools/pages.json upsert 1"
  );
}

$mode = strtolower($mode);
$allowed_modes = ['create', 'upsert', 'warn', 'skip'];
if (!in_array($mode, $allowed_modes, true)) {
  WP_CLI::error('--mode must be one of: create, upsert, warn, skip');
}

if (!file_exists($path)) {
  WP_CLI::error("File not found: {$path}");
}

$json = file_get_contents($path);
$data = json_decode($json, true);

if (!is_array($data)) {
  WP_CLI::error("Invalid JSON: {$path}");
}

/**
 * JSONフォーマット想定:
 * [
 *   {"title":"About","slug":"about","parent_slug":"","status":"draft","content":""},
 *   {"title":"Message","slug":"message","parent_slug":"about","status":"draft","content":""}
 * ]
 * ※ `parent` キーも互換で受け付けます（`parent_slug` 優先）。
 */

function find_page_id_by_path_slug($slug_path) {
  // "about/message" のようなパスを page_path で探す
  $page = get_page_by_path($slug_path, OBJECT, 'page');
  return $page ? (int)$page->ID : 0;
}

function find_page_id_by_slug($slug) {
  // 親解決などで単一スラッグ検索したい場合
  $page = get_page_by_path($slug, OBJECT, 'page');
  return $page ? (int)$page->ID : 0;
}

$created = 0;
$updated = 0;
$skipped = 0;

// 親 → 子の順に処理するため、スラッグの階層の深さでソート
// 例: "activity" (depth 0) が先、"activity/forum" (depth 1) が後
usort($data, function ($a, $b) {
  $a_slug = is_array($a) ? (string)($a['slug'] ?? '') : '';
  $b_slug = is_array($b) ? (string)($b['slug'] ?? '') : '';
  $a_parent = is_array($a) ? (string)($a['parent_slug'] ?? ($a['parent'] ?? '')) : '';
  $b_parent = is_array($b) ? (string)($b['parent_slug'] ?? ($b['parent'] ?? '')) : '';

  $a_path = $a_slug;
  if ($a_parent !== '' && strpos($a_slug, '/') === false) {
    $a_path = rtrim($a_parent, '/') . '/' . $a_slug;
  }

  $b_path = $b_slug;
  if ($b_parent !== '' && strpos($b_slug, '/') === false) {
    $b_path = rtrim($b_parent, '/') . '/' . $b_slug;
  }

  $a_depth = substr_count($a_path, '/');
  $b_depth = substr_count($b_path, '/');

  if ($a_depth === $b_depth) {
    // 深さが同じなら安定化のためにパス文字列で比較
    return strcmp($a_path, $b_path);
  }

  return $a_depth <=> $b_depth;
});

// dry-run 時は DB に書き込まないため get_page_by_path で親が見つからない。
// そのため「この実行で作成予定の親」を仮想的に解決できるように保持する。
$planned_pages = []; // slug_path => true

foreach ($data as $i => $item) {
  if (!is_array($item)) {
    WP_CLI::warning("Row {$i}: item is not an object/array. skipped.");
    $skipped++;
    continue;
  }

  $slug = $item['slug'] ?? '';
  $title = $item['title'] ?? ($item['text'] ?? '');
  $status = $item['status'] ?? 'publish';
  $content = $item['content'] ?? '';

  // `parent_slug` を優先し、互換で `parent` も受け付ける
  $parent_slug = $item['parent_slug'] ?? ($item['parent'] ?? null);

  // WordPressの階層ページの検索は「フルパス（about/message）」が確実。
  // JSONの `slug` が単一スラッグでも、parent があればパスを組み立てて扱う。
  $slug_path = $slug;
  if (is_string($parent_slug) && $parent_slug !== '' && strpos((string) $slug, '/') === false) {
    $slug_path = rtrim($parent_slug, '/') . '/' . $slug;
  }

  if (!$slug || !$title) {
    WP_CLI::warning("Row {$i}: slug/title missing. skipped.");
    $skipped++;
    continue;
  }

  // 既存ページ判定（フルパスslug優先）
  $existing_id = find_page_id_by_path_slug($slug_path);

  // 親解決（parentがslugならそれをIDに）
  $parent_id = 0;
  if (is_string($parent_slug) && $parent_slug !== '') {
    $parent_id = find_page_id_by_path_slug($parent_slug);

    // dry-run の場合は、今まさに作成予定の親を仮想的に「存在する」とみなす
    if (!$parent_id && $dry_run && isset($planned_pages[$parent_slug])) {
      $parent_id = -1; // 仮の値（ログ表示用）
    }

    if (!$parent_id) {
      WP_CLI::warning("Row {$i}: parent not found by slug '{$parent_slug}' (child: '{$slug_path}'). parent=0 で作成/更新します。");
      $parent_id = 0;
    }
  }

  // modeごとの動き
  if ($existing_id) {
    if ($mode === 'create') {
      WP_CLI::warning("Exists (mode=create): {$slug_path} (ID: {$existing_id}) -> skip");
      $skipped++;
      continue;
    }

    if ($mode === 'warn') {
      WP_CLI::warning("Slug collision (mode=warn): {$slug_path} (ID: {$existing_id}) -> skip");
      $skipped++;
      continue;
    }

    if ($mode === 'skip') {
      WP_CLI::log("Skip existing (mode=skip): {$slug_path} (ID: {$existing_id})");
      $skipped++;
      continue;
    }

    // upsert: 更新
    $postarr = [
      'ID' => $existing_id,
      'post_title' => $title,
      'post_status' => $status,
      'post_parent' => $parent_id,
      // slugは基本変えない（既存のパス維持）
      // 'post_name' => basename($slug), // 変えたいならここを有効化
    ];

    // contentがJSONにあるときだけ更新したい場合は条件分岐
    if (array_key_exists('content', $item)) {
      $postarr['post_content'] = $content;
    }

    if ($dry_run) {
      WP_CLI::log("[dry-run] Update: {$slug_path} (ID: {$existing_id})");
      $planned_pages[$slug_path] = true;
      $updated++;
      continue;
    }

    $result = wp_update_post($postarr, true);
    if (is_wp_error($result)) {
      WP_CLI::warning("Update failed: {$slug_path} -> " . $result->get_error_message());
      $skipped++;
      continue;
    }

    WP_CLI::success("Updated: {$slug_path} (ID: {$existing_id})");
    $planned_pages[$slug_path] = true;
    $updated++;
    continue;
  }

  // 既存なし → 新規作成
  if ($mode === 'warn' || $mode === 'skip') {
    // warn/skip は「衝突時の挙動」なので、未存在なら作成してよい
  }

  $post_name = basename($slug_path); // "about/message" -> "message"
  $postarr = [
    'post_type' => 'page',
    'post_title' => $title,
    'post_status' => $status,
    'post_content' => $content,
    'post_parent' => $parent_id,
    'post_name' => $post_name,
  ];

  if ($dry_run) {
    $pid_log = ($parent_id === -1) ? 'PENDING' : (string)$parent_id;
    WP_CLI::log("[dry-run] Create: {$slug_path} (parent_id: {$pid_log})");
    $planned_pages[$slug_path] = true;
    $created++;
    continue;
  }

  $new_id = wp_insert_post($postarr, true);
  if (is_wp_error($new_id)) {
    WP_CLI::warning("Create failed: {$slug_path} -> " . $new_id->get_error_message());
    $skipped++;
    continue;
  }

  WP_CLI::success("Created: {$slug_path} (ID: {$new_id})");
  $planned_pages[$slug_path] = true;
  $created++;
}

WP_CLI::log("Done. created={$created}, updated={$updated}, skipped={$skipped}, mode={$mode}, dry-run=" . ($dry_run ? '1' : '0'));

