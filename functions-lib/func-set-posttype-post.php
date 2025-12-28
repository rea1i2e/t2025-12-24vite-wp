<?php
declare(strict_types=1);

/**
 * 「投稿」ラベル名を返す（必要ならフィルタで差し替え可能）
 */
if (!function_exists('ty_post_label_name')) {
	function ty_post_label_name(): string
	{
		/**
		 * @param string $name デフォルトの表示名
		 */
		return (string) apply_filters('ty_post_label_name', 'お知らせ');
	}
}

if (!function_exists('ty_change_post_object_labels')) {
	function ty_change_post_object_labels(): void
	{
		if (!is_admin()) return;

		global $wp_post_types;
		if (!isset($wp_post_types['post']) || !is_object($wp_post_types['post'])) return;
		if (!isset($wp_post_types['post']->labels) || !is_object($wp_post_types['post']->labels)) return;

		$name = ty_post_label_name();
		$labels = $wp_post_types['post']->labels;

		$labels->name = $name;
		$labels->singular_name = $name;
		$labels->add_new_item = "{$name}の新規追加";
		$labels->edit_item = "{$name}の編集";
		$labels->new_item = "新規{$name}";
		$labels->view_item = "{$name}を表示";
		$labels->search_items = "{$name}を検索";
		$labels->not_found = "{$name}が見つかりませんでした";
		$labels->not_found_in_trash = "ゴミ箱に{$name}は見つかりませんでした";
	}
}

if (!function_exists('ty_change_post_menu_labels')) {
	function ty_change_post_menu_labels(): void
	{
		if (!is_admin()) return;

		global $menu, $submenu;
		if (!is_array($menu) || !is_array($submenu)) return;

		$name = ty_post_label_name();
		$menu[5][0] = $name;
		if (isset($submenu['edit.php'][5][0])) $submenu['edit.php'][5][0] = "{$name}一覧";
		if (isset($submenu['edit.php'][10][0])) $submenu['edit.php'][10][0] = "新しい{$name}";
	}
}

add_action('init', 'ty_change_post_object_labels');
add_action('admin_menu', 'ty_change_post_menu_labels');
