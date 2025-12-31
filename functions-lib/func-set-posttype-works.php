<?php

/**
 * カスタム投稿works
 * ・投稿タイプの設置
 * ・タクソノミーの設置
 * ・メインクエリの出力件数変更
 */

declare(strict_types=1);

add_action('init', 'ty_register_post_type_works');

function ty_register_post_type_works(): void
	{
		$singular = '制作実績';
		$plural = '制作実績'; // 複数形のラベル

		$labels = [
			'name' => $plural,
			'singular_name' => $singular,
			'add_new' => '新規追加',
			'add_new_item' => "{$singular}を追加",
			'edit_item' => "{$singular}を編集",
			'new_item' => "新規{$singular}",
			'view_item' => "{$singular}を表示",
			'search_items' => "{$singular}を検索",
			'not_found' => "{$singular}が見つかりません",
			'not_found_in_trash' => "ゴミ箱に{$singular}が見つかりません",
			'all_items' => "{$singular}一覧",
			'menu_name' => $plural,
		];

		register_post_type('works', [
			'labels' => $labels,
			'public' => true,
			'has_archive' => true,
			'menu_position' => 6,
			'menu_icon' => 'dashicons-megaphone',
			'show_in_rest' => true,
			'supports' => ['title', 'editor', 'thumbnail', 'revisions'],
			'rewrite' => [ // パーマリンク設定の影響を受けないようにする
				'slug' => 'works',
				'with_front' => false,
			],
		]);

		// カテゴリタクソノミーを登録（フロントには出さず、管理画面/RESTで利用）
		$works_category_label = '制作実績カテゴリ';

		register_taxonomy(
			'works_category', // タクソノミーの名前
			'works', // 関連付ける投稿タイプ
			[
				'label' => $works_category_label, // 管理画面に表示される名前
				'labels' => [
					'name' => $works_category_label,
					'singular_name' => $works_category_label,
					'all_items' => "すべての{$works_category_label}",
					'edit_item' => "{$works_category_label}を編集",
					'view_item' => "{$works_category_label}を表示",
					'update_item' => "{$works_category_label}を更新",
					'add_new_item' => "新しい{$works_category_label}を追加",
					'new_item_name' => "新しい{$works_category_label}名",
					'search_items' => "{$works_category_label}を検索",
					'popular_items' => "よく使われている{$works_category_label}",
					'separate_items_with_commas' => "{$works_category_label}をカンマで区切る",
					'add_or_remove_items' => "{$works_category_label}を追加または削除",
					'choose_from_most_used' => "よく使われている{$works_category_label}から選択",
					'not_found' => "{$works_category_label}が見つかりませんでした",
				],
				'public' => false, // フロントエンドには表示しない
				'hierarchical' => true, // カテゴリ型（階層あり）
				'show_ui' => true,
				'show_in_rest' => true, // ブロックエディタ/RESTで使用可能
				'show_admin_column' => true, // 管理画面の一覧に表示
				'query_var' => true,
				'rewrite' => false, // URLリライトは不要
			]
		);
}

function ty_custom_main_query($query) : void
{
  if (is_admin() || !$query->is_main_query()) return;
  if (is_post_type_archive('works')) $query->set('posts_per_page', 4);
}
add_action('pre_get_posts', 'ty_custom_main_query');
