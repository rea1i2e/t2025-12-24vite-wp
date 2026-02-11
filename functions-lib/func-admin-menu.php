<?php

declare(strict_types=1);

/**
 * 管理画面メニューのカスタマイズ
 *
 * - 非表示にするメニューを配列で管理
 * - 指定した固定ページをスラッグでトップレベルに常時表示（Local・テスト・本番でIDのズレを防ぐ）
 * - 固定ページ編集権限があり、かつ対象ページを編集できるユーザーのみに表示
 *
 * 注意: edit.php を非表示にすると「投稿」メニューが消え、func-set-posttype-post.php のラベル変更（お知らせ）は見た目に効かなくなる。
 * 「お知らせ」として残す場合は $ty_admin_menu_remove_slugs に edit.php を入れないこと。
 */

$ty_admin_menu_remove_slugs = [
	'edit.php',          // デフォルト投稿
	'edit-comments.php', // コメント
];

$ty_admin_page_menu_slugs = [
	'top', // 固定ページスラッグで指定（必要に応じて 'parent/child' 形式も指定可）
	// 'about',
];

add_action('admin_menu', function () use ($ty_admin_menu_remove_slugs, $ty_admin_page_menu_slugs) {
	// メニュー非表示
	foreach ($ty_admin_menu_remove_slugs as $menu_slug) {
		remove_menu_page($menu_slug);
	}

	// 固定ページをトップレベルに追加
	foreach ($ty_admin_page_menu_slugs as $page_path) {
		$page = get_page_by_path($page_path, OBJECT, 'page');
		if (!$page) {
			continue;
		}

		// 対象ページを編集できるユーザーだけに表示
		if (!current_user_can('edit_post', $page->ID)) {
			continue;
		}

		// 編集画面への直リンク（リダイレクト不要）
		$menu_slug = 'post.php?post=' . $page->ID . '&action=edit';

		add_menu_page(
			$page->post_title . 'を編集',
			$page->post_title,
			'edit_pages', // add_menu_page には edit_post を渡さない
			$menu_slug,
			'',
			'dashicons-admin-page',
			8
		);
	}
}, 10, 0);

// 壊れた形式のメニュー項目を除去（プラグイン等が [1][2][4] を欠いた項目を追加していると menu.php でエラーになるため）
add_action('admin_menu', function (): void {
	global $menu;
	if (!is_array($menu)) {
		return;
	}
	foreach ($menu as $id => $item) {
		if (!is_array($item) || !isset($item[1], $item[2], $item[4])) {
			unset($menu[$id]);
		}
	}
}, 999);
