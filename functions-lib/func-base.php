<?php

declare(strict_types=1);
/**
 * テーマ基本セットアップ
 *
 * - テーマサポート（title-tag / アイキャッチ / HTML5対応）を有効化
 * - `after_setup_theme` で実行される
 */
function ty_setup(): void
{
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'script',
		'style',
	]);
}
add_action('after_setup_theme', 'ty_setup');

/**
 * コメント／ピンバック受付のクローズ
 *
 * フロントにコメント UI が無くても wp-comments-post.php 等の受付口は残るため、
 * comments_open / pings_open を閉じる。管理メニュー非表示（func-admin-menu.php）とは別。
 *
 * 再有効化: テーマ内で define('TY_ENABLE_COMMENTS', true); するか、下の定数を true にする。
 *
 * @see https://github.com/rea1i2e/t2025-12-24vite-wp/issues/5
 */
if (!defined('TY_ENABLE_COMMENTS')) {
	define('TY_ENABLE_COMMENTS', false);
}

if (!TY_ENABLE_COMMENTS) {
	add_filter('comments_open', '__return_false', 20);
	add_filter('pings_open', '__return_false', 20);
}

/* 絵文字の無効化（モダンブラウザでは、標準で対応しているため無効化してパフォーマンスを上げる） */
function ty_disable_emoji(): void
{
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_action('wp_enqueue_scripts', 'wp_enqueue_emoji_styles');
	remove_action('admin_print_styles', 'print_emoji_styles');
	remove_filter('the_content_feed', 'wp_staticize_emoji');
	remove_filter('comment_text_rss', 'wp_staticize_emoji');
	remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
}
add_action('init', 'ty_disable_emoji');
