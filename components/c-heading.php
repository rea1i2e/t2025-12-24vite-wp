<?php
/**
 * 見出しコンポーネント
 *
 * =========================================
 * 使い方（例）
 * =========================================
 *
 * ▼ 基本（en は <p> / ja は <h2>）
 *   get_template_part('components/c-heading', null, [
 *     'text_en' => 'NEWS',
 *     'text_ja' => 'お知らせ',
 *   ]);
 *
 * ▼ ja を h1 にする
 *   get_template_part('components/c-heading', null, [
 *     'text_en' => 'ABOUT',
 *     'text_ja' => '私たちについて',
 *     'tag' => 'h1',
 *   ]);
 *
 * ▼ en も見出しタグにする（例: h3）
 *   get_template_part('components/c-heading', null, [
 *     'text_en' => 'CONTACT',
 *     'text_ja' => 'お問い合わせ',
 *     'en_tag' => 'h3',
 *     'tag' => 'h2',
 *   ]);
 *
 * @param array $args 引数配列
 *   - text_en (string, 必須): 英語テキスト（HTMLタグを含む可能性がある）
 *   - text_ja (string, 必須): 日本語テキスト
 *   - en_tag (string, オプション): 英語テキストのタグ（例: 'h1'〜'h6' / 'p'）
 *   - tag (string, オプション): 日本語見出しのタグ（例: 'h1'〜'h6' / 'p'）
 */

declare(strict_types=1);

// デフォルト値を設定
$default_args = [
	'text_en' => '',
	'text_ja' => '',
	'en_tag' => 'p',
	'tag' => 'h2',
];

$heading_args = wp_parse_args((is_array($args) ? $args : []), $default_args);

// 許可されたタグのみを使用（セキュリティ対策）
$allowed_tags = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
$en_tag = in_array($heading_args['en_tag'], $allowed_tags, true) ? (string) $heading_args['en_tag'] : 'p';
$ja_tag = in_array($heading_args['tag'], $allowed_tags, true) ? (string) $heading_args['tag'] : 'h2';
$wrap_tag = ($en_tag === 'p' && $ja_tag === 'p') ? 'div' : 'hgroup';

$en = (string) $heading_args['text_en'];
$ja = (string) $heading_args['text_ja'];
?>
<<?php echo $wrap_tag; ?> class="c-heading">
  <<?php echo $en_tag; ?> class="c-heading__en" data-fadein><?php echo wp_kses_post($en); ?></<?php echo $en_tag; ?>>
  <<?php echo $ja_tag; ?> class="c-heading__ja" data-fadein><?php echo esc_html($ja); ?></<?php echo $ja_tag; ?>>
</<?php echo $wrap_tag; ?>>

