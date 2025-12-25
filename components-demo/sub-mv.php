<?php
// デフォルト値を設定
$default_args = [
  'tag' => 'h1',
  'image' => [
    'file' => null,
    'directory' => '',
    'alt' => '',
    'sp' => false,
    'type' => 'jpg',
    'width' => 2880,
    'height' => 780,
    'spWidth' => 390,
    'spHeight' => 600,
    'lazy' => false
  ]
];

$sub_mv_args = wp_parse_args($args, $default_args);

// 画像パラメータのマージ処理（既存の値を保持しつつデフォルト値で補完）
if (isset($args['image']) && is_array($args['image'])) {
  $sub_mv_args['image'] = wp_parse_args($args['image'], $default_args['image']);
}

// 許可されたタグのみを使用（セキュリティ対策）
$allowed_tags = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
$title_ja_tag = in_array($sub_mv_args['tag'], $allowed_tags) ? $sub_mv_args['tag'] : 'p';
$title_wrap_tag = $title_ja_tag === 'p' ? 'div' : 'hgroup';
?>
<div class="p-sub-mv l-sub-mv" id="js-mv">
  <div class="p-sub-mv__inner l-inner">
    <<?php echo $title_wrap_tag; ?> class="p-sub-mv__title">
      <<?php echo $title_ja_tag; ?> class="p-sub-mv__title-ja"><?php echo esc_html($sub_mv_args['title_ja']); ?></<?php echo $title_ja_tag; ?>>
      <?php if (!empty($sub_mv_args['title_en'])) : ?>
        <p class="p-sub-mv__title-en"><?php echo esc_html($sub_mv_args['title_en']); ?></p>
      <?php endif; ?>
    </<?php echo $title_wrap_tag; ?>>
  </div>
  <?php if ($sub_mv_args['image']['file']) : ?>
    <figure class="p-sub-mv__image">
      <?php get_template_part('components/picture', null, $sub_mv_args['image']); ?>
    </figure>
  <?php endif; ?>
</div>
<?php get_template_part('components/breadcrumb'); ?>


