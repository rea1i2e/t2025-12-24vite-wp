<?php
declare(strict_types=1);

// デフォルト値を設定
$default_args = [
	'tag' => 'h1',
	'title_ja' => '',
	'title_en' => '',
	'image' => [
		'file' => null,
		'fileSp' => null,
		'alt' => '',
	],
];

$sub_mv_args = wp_parse_args((is_array($args) ? $args : []), $default_args);

// 画像パラメータのマージ処理（既存の値を保持しつつデフォルト値で補完）
if (isset($args['image']) && is_array($args['image'])) {
	$sub_mv_args['image'] = wp_parse_args($args['image'], $default_args['image']);
}

// 許可されたタグのみを使用（セキュリティ対策）
$allowed_tags = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
$title_ja_tag = in_array($sub_mv_args['tag'], $allowed_tags, true) ? (string) $sub_mv_args['tag'] : 'p';
$title_wrap_tag = ($title_ja_tag === 'p') ? 'div' : 'hgroup';

// 画像URLを解決（URLが取れない場合は画像を出さない）
$pc_src = '';
$sp_src = '';
if (!empty($sub_mv_args['image']['file']) && function_exists('t2025_theme_image_url')) {
	$pc_src = t2025_theme_image_url((string) $sub_mv_args['image']['file']);
	if (!empty($sub_mv_args['image']['fileSp'])) {
		$sp_src = t2025_theme_image_url((string) $sub_mv_args['image']['fileSp']);
	}
}

$has_image = ($pc_src !== '');
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
  <?php if ($has_image) : ?>
    <figure class="p-sub-mv__image">
      <picture>
        <?php if ($sp_src !== '') : ?>
          <source srcset="<?php echo esc_url($sp_src); ?>" media="(max-width: 767px)">
        <?php endif; ?>
        <img src="<?php echo esc_url($pc_src); ?>" alt="<?php echo esc_attr((string) $sub_mv_args['image']['alt']); ?>">
      </picture>
    </figure>
  <?php endif; ?>
</div>
<?php get_template_part('components/breadcrumb'); ?>