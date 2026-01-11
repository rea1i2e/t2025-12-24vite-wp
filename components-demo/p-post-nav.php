<?php
/**
 * 投稿ナビゲーションコンポーネント
 *
 * =========================================
 * 使い方（例）
 * =========================================
 *
 * ▼ 基本（デフォルト投稿・カスタム投稿の両方に対応）
 *   get_template_part('components-demo/p-post-nav');
 *
 * ▼ テキストをカスタマイズ
 *   get_template_part('components-demo/p-post-nav', null, [
 *     'prev_text' => '前の記事',
 *     'next_text' => '次の記事',
 *     'list_text' => '記事一覧',
 *   ]);
 *
 * @param array $args 引数配列
 *   - prev_text (string, オプション): 前の投稿へのリンクテキスト（デフォルト: '前へ'）
 *   - next_text (string, オプション): 次の投稿へのリンクテキスト（デフォルト: '次へ'）
 *   - list_text (string, オプション): 一覧ページへのリンクテキスト（デフォルト: '一覧へ'）
 *   - in_same_term (bool, オプション): 同じタクソノミー内の投稿のみを取得するか（デフォルト: false）
 *   - taxonomy (string, オプション): タクソノミー名（in_same_term が true の場合に使用）
 */

declare(strict_types=1);

$args = isset($args) && is_array($args) ? $args : [];

$prev_text = isset($args['prev_text']) ? (string) $args['prev_text'] : '前へ';
$next_text = isset($args['next_text']) ? (string) $args['next_text'] : '次へ';
$list_text = isset($args['list_text']) ? (string) $args['list_text'] : '一覧へ';
$in_same_term = isset($args['in_same_term']) ? (bool) $args['in_same_term'] : false;
$taxonomy = isset($args['taxonomy']) ? (string) $args['taxonomy'] : '';

// 投稿タイプのアーカイブURLを取得
$post_type = get_post_type();
if ($post_type === 'post') {
  // デフォルト投稿の場合、表示設定を確認
  $show_on_front = get_option('show_on_front');
  if ($show_on_front === 'page') {
    // 固定ページがフロントページの場合、投稿一覧ページのIDを取得
    $page_for_posts = get_option('page_for_posts');
    $archive_url = $page_for_posts ? get_permalink($page_for_posts) : home_url();
  } else {
    // 最新の投稿がフロントページの場合
    $archive_url = home_url();
  }
} else {
  // カスタム投稿タイプの場合
  $archive_url = get_post_type_archive_link($post_type);
}

// 前後の投稿を取得
// $taxonomyが指定されている場合のみ第4引数を渡す
if ($taxonomy !== '') {
  $prev_post = get_adjacent_post($in_same_term, '', true, $taxonomy);
  $next_post = get_adjacent_post($in_same_term, '', false, $taxonomy);
} else {
  $prev_post = get_adjacent_post($in_same_term, '', true);
  $next_post = get_adjacent_post($in_same_term, '', false);
}
?>

<div class="p-post-nav">
  <div class="p-post-nav__prev">
    <?php if ($prev_post) : ?>
      <a href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>" rel="prev"><?php echo esc_html($prev_text); ?></a>
    <?php endif; ?>
  </div>
  <?php if ($archive_url) : ?>
    <div class="p-post-nav__list">
      <a href="<?php echo esc_url($archive_url); ?>"><?php echo esc_html($list_text); ?></a>
    </div>
  <?php endif; ?>
  <div class="p-post-nav__next">
    <?php if ($next_post) : ?>
      <a href="<?php echo esc_url(get_permalink($next_post->ID)); ?>" rel="next"><?php echo esc_html($next_text); ?></a>
    <?php endif; ?>
  </div>
</div>

