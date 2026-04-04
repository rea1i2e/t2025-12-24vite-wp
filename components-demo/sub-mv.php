<?php
declare(strict_types=1);

/**
 * sub-mv（サブMV）コンポーネント
 *
 * =========================================
 * 使い方（例）
 * =========================================
 *
 * ▼ 基本（画像なし）
 * get_template_part('components-demo/sub-mv', null, [
 *   'tag' => 'h1', // p/h1-h6（それ以外はp扱い）
 *   'title_ja' => '',
 *   'title_en' => '',
 * ]);
 *
 * ▼ 画像あり（PCのみ）
 * get_template_part('components-demo/sub-mv', null, [
 *   'tag' => 'h1',
 *   'title_ja' => '',
 *   'title_en' => '',
 *   'image' => [
 *     'file' => 'common/sub-mv.jpg',   // `src/assets/images/` 配下の相対パス
 *     'alt'  => '',                   // alt（省略/空の場合は alt="" になる）
 *   ],
 * ]);
 *
 * ▼ 画像あり（PC/SP は命名規則で自動: file が name.png なら SP は name_sp.png）
 * get_template_part('components-demo/sub-mv', null, [
 *   'tag' => 'h1',
 *   'title_ja' => '',
 *   'title_en' => '',
 *   'image' => [
 *     'file' => 'common/sub-mv.jpg',
 *     'alt'  => '',
 *   ],
 * ]);
 *
 * 補足:
 * - 画像は `ty_get_picture_img()` で取得し echo（変数に格納してから出力するため）。そのまま出したい場合は `ty_picture_img()`
 */

// デフォルト値を設定
$default_args = [
	'tag' => 'h1',
	'title_ja' => '',
	'title_en' => '',
	'image' => [
		'file' => null,
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
$picture_html = '';
if (!empty($sub_mv_args['image']['file'])) {
	$pc_file = (string) $sub_mv_args['image']['file'];
	$alt = (string) $sub_mv_args['image']['alt'];

	$picture_html = ty_get_picture_img($pc_file, null, $alt, true, 'fetchpriority="high"');
}

$has_image = ($picture_html !== '');
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
      <?php echo $picture_html; ?>
    </figure>
  <?php endif; ?>
</div>
<?php get_template_part('components-demo/breadcrumb'); ?>