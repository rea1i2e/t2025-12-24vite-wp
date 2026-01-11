<?php
/**
 * ページネーションコンポーネント
 *
 * =========================================
 * 使い方（例）
 * =========================================
 *
 * ▼ 基本
 *   get_template_part('components-demo/p-pagination');
 *
 * ▼ テキストをカスタマイズ
 *   get_template_part('components-demo/p-pagination', null, [
 *     'prev_text' => '前のページ',
 *     'next_text' => '次のページ',
 *   ]);
 *
 * @param array $args 引数配列
 *   - prev_text (string, オプション): 前へボタンのテキスト（デフォルト: '前へ'）
 *   - next_text (string, オプション): 次へボタンのテキスト（デフォルト: '次へ'）
 */

declare(strict_types=1);

$args = isset($args) && is_array($args) ? $args : [];

// 変更可能な設定（引数で受け取る）
$prev_text = isset($args['prev_text']) ? (string) $args['prev_text'] : '前へ';
$next_text = isset($args['next_text']) ? (string) $args['next_text'] : '次へ';

// 投稿がない場合、または1ページのみの場合は表示しない
global $wp_query;
if (!$wp_query->have_posts() || $wp_query->max_num_pages <= 1) {
  return;
}
?>
<div class="p-pagination">
  <?php
    the_posts_pagination([
      'mid_size' => 2, // 投稿タイプに依存しない設定（コンポーネント内で固定）
      'end_size' => 1, // 投稿タイプに依存しない設定（コンポーネント内で固定）
      'prev_text' => $prev_text,
      'next_text' => $next_text,
    ]);
  ?>
</div>
