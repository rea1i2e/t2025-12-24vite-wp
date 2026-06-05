<?php

declare(strict_types=1);

/**
 * 投稿タイプ「投稿」の管理画面カスタム
 *
 * 案件ごとの切り替えは下記「案件設定」の定数のみ編集する。
 * フィルター `ty_post_label_name` / `ty_post_hide_category_ui` / `ty_post_hide_tag_ui` で上書きも可能。
 */

// --- 案件設定 ---
/** 管理画面の表示名（標準 post） */
const TY_POST_LABEL_NAME = 'お知らせ';

/** true … カテゴリーの管理 UI を非表示（タクソノミー自体は残す） */
const TY_POST_HIDE_CATEGORY_UI = false;

/** true … タグの管理 UI を非表示（タクソノミー自体は残す） */
const TY_POST_HIDE_TAG_UI = false;

function ty_post_label_name(): string
{
	/**
	 * @param string $name デフォルトの表示名
	 */
	return (string) apply_filters('ty_post_label_name', TY_POST_LABEL_NAME);
}

function ty_post_is_category_ui_hidden(): bool
{
	return (bool) apply_filters('ty_post_hide_category_ui', TY_POST_HIDE_CATEGORY_UI);
}

function ty_post_is_tag_ui_hidden(): bool
{
	return (bool) apply_filters('ty_post_hide_tag_ui', TY_POST_HIDE_TAG_UI);
}

function ty_change_post_object_labels(): void
{
	if (!is_admin()) {
		return;
	}

	global $wp_post_types;
	if (!isset($wp_post_types['post']) || !is_object($wp_post_types['post'])) {
		return;
	}
	if (!isset($wp_post_types['post']->labels) || !is_object($wp_post_types['post']->labels)) {
		return;
	}

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

function ty_change_post_menu_labels(): void
{
	if (!is_admin()) {
		return;
	}

	global $menu, $submenu;
	if (!is_array($menu) || !is_array($submenu)) {
		return;
	}

	$name = ty_post_label_name();
	$menu[5][0] = $name;
	if (isset($submenu['edit.php'][5][0])) {
		$submenu['edit.php'][5][0] = "{$name}一覧";
	}
	if (isset($submenu['edit.php'][10][0])) {
		$submenu['edit.php'][10][0] = "新しい{$name}";
	}
}

/**
 * コアタクソノミーの管理 UI を無効化（init@999 でコア登録後に上書き）
 */
function ty_post_disable_taxonomy_admin(): void
{
	$args = [
		'show_ui' => false,
		'show_admin_column' => false,
		'show_in_nav_menus' => false,
		'show_in_rest' => false,
		'meta_box_cb' => false,
	];

	if (ty_post_is_category_ui_hidden()) {
		register_taxonomy('category', 'post', $args);
	}
	if (ty_post_is_tag_ui_hidden()) {
		register_taxonomy('post_tag', 'post', $args);
	}
}

/**
 * 編集画面メタボックス除去（Classic Editor 等のフォールバック）
 */
function ty_post_hide_taxonomy_meta_boxes(): void
{
	if (ty_post_is_category_ui_hidden()) {
		remove_meta_box('categorydiv', 'post', 'side');
	}
	if (ty_post_is_tag_ui_hidden()) {
		remove_meta_box('tagsdiv-post_tag', 'post', 'side');
	}
}

/**
 * 投稿一覧からカテゴリー・タグ列を除去
 *
 * @param array<string, string> $columns
 * @return array<string, string>
 */
function ty_post_remove_taxonomy_columns(array $columns): array
{
	if (ty_post_is_category_ui_hidden()) {
		unset($columns['categories']);
	}
	if (ty_post_is_tag_ui_hidden()) {
		unset($columns['tags']);
	}

	return $columns;
}

/**
 * クイック編集のタクソノミー欄を非表示（post の category / post_tag のみ）
 */
function ty_post_quick_edit_hide_taxonomy(bool $show, string $taxonomy, string $post_type): bool
{
	if ($post_type !== 'post') {
		return $show;
	}
	if ($taxonomy === 'category' && ty_post_is_category_ui_hidden()) {
		return false;
	}
	if ($taxonomy === 'post_tag' && ty_post_is_tag_ui_hidden()) {
		return false;
	}

	return $show;
}

/**
 * 投稿メニュー配下のカテゴリー・タグサブメニューを除去（保険）
 */
function ty_post_remove_taxonomy_submenus(): void
{
	if (ty_post_is_category_ui_hidden()) {
		remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=category');
	}
	if (ty_post_is_tag_ui_hidden()) {
		remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=post_tag');
	}
}

/**
 * カテゴリー・タグ UI 非表示のフック登録
 */
function ty_post_register_taxonomy_hide_hooks(): void
{
	if (!ty_post_is_category_ui_hidden() && !ty_post_is_tag_ui_hidden()) {
		return;
	}

	add_action('init', 'ty_post_disable_taxonomy_admin', 999);
	add_action('admin_init', 'ty_post_hide_taxonomy_meta_boxes');
	add_action('admin_menu', 'ty_post_remove_taxonomy_submenus');
	add_filter('manage_posts_columns', 'ty_post_remove_taxonomy_columns');
	add_filter('quick_edit_show_taxonomy', 'ty_post_quick_edit_hide_taxonomy', 10, 3);
}

add_action('init', 'ty_change_post_object_labels');
add_action('admin_menu', 'ty_change_post_menu_labels');
add_action('after_setup_theme', 'ty_post_register_taxonomy_hide_hooks');
