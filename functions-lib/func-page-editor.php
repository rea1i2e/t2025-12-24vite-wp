<?php

declare(strict_types=1);

/**
 * 固定ページの編集画面：本文を使わないテンプレートでは本文エディタを非表示にする。
 * 抜粋は残す（SEO・OGP用）。
 *
 * 対象ページはクラシック編集に切り替え、editor サポートを外して本文エディタの描画をスキップする。
 * ブロックエディタ用のフォールバックは行わない。
 *
 * 判定は「実質的に使われるテンプレート」で行う。
 * - テンプレートを明示選択している場合: _wp_page_template の値を使用
 * - 未選択（default）の場合: page-{スラッグ}.php とみなす
 * 対象テンプレートは $ty_page_editor_no_content_templates に列挙する。
 */

/** @var string[] 本文を表示しないページテンプレート（ファイル名） */
$ty_page_editor_no_content_templates = [
	'page-about.php',
	'page-contact.php',
	'page-news.php', // 投稿一覧
];

/**
 * 指定した投稿が「本文を使わない」テンプレートかどうかを判定する。
 */
function ty_page_uses_no_content_template(int $post_id): bool
{
	global $ty_page_editor_no_content_templates;
	$template = get_post_meta($post_id, '_wp_page_template', true);
	if ($template !== '' && $template !== 'default') {
		return in_array($template, $ty_page_editor_no_content_templates, true);
	}
	$post = get_post($post_id);
	if (!$post || $post->post_type !== 'page') {
		return false;
	}
	$effective = 'page-' . $post->post_name . '.php';
	return in_array($effective, $ty_page_editor_no_content_templates, true);
}

// 対象ページはクラシック編集画面にし、本文ボックスを削除できるようにする（優先度 99 で他より後に実行）
add_filter('use_block_editor_for_post', function (bool $use_block_editor, \WP_Post $post): bool {
	if ($post->post_type !== 'page') {
		return $use_block_editor;
	}
	if (!ty_page_uses_no_content_template((int) $post->ID)) {
		return $use_block_editor;
	}
	return false;
}, 99, 2);

/**
 * クラシック編集画面で本文エディタを非表示にする。
 * #postdivrich はメタボックスではなく post_type_supports('editor') で直接描画されるため、
 * remove_post_type_support で editor サポートを外して描画自体をスキップする。
 * add_meta_boxes_page はエディタ描画前に実行されるので、ここで外せば間に合う。
 */
function ty_page_editor_remove_content_editor(\WP_Post $post): void {
	if (!ty_page_uses_no_content_template((int) $post->ID)) {
		return;
	}
	remove_post_type_support('page', 'editor');
}

add_action('add_meta_boxes_page', 'ty_page_editor_remove_content_editor', 999);

// 対象ページの編集画面に、本文エディタを非表示にしている旨の通知を表示する
add_action('admin_notices', function (): void {
	$screen = get_current_screen();
	if (!$screen || $screen->base !== 'post' || $screen->post_type !== 'page') {
		return;
	}
	$post_id = (int) ($_GET['post'] ?? 0);
	if ($post_id <= 0 || !ty_page_uses_no_content_template($post_id)) {
		return;
	}
	echo '<div class="notice notice-info"><p>';
	echo esc_html__('このページでは本文エディタを非表示にしています。', 'flavor');
	echo '（テーマ設定: <code>functions-lib/func-page-editor.php</code>）';
	echo '</p></div>';
});
