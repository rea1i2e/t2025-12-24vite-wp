<?php
declare(strict_types=1);
/**
 * 自動生成されるページの表示を防ぐ（404エラーにする）
 *
 * =========================================
 * 使い方（例）
 * =========================================
 *
 * ▼ 投稿者一覧ページを404にする（現在の設定）
 *   if (is_author()) { ... }
 *
 * ▼ 複数のページタイプを404にする
 *   if (is_category() || is_tag() || is_date() || is_author()) { ... }
 *
 * 利用可能な条件分岐:
 * - デフォルト投稿詳細ページ: is_singular('post')
 * - 投稿者一覧ページ: is_author()
 * - カテゴリーページ: is_category()
 * - タグページ: is_tag()
 * - 日付ページ: is_date()
 * - 検索結果ページ: is_search()
 * - 404ページ: is_404()
 */

add_action('template_redirect', function () {
	if (is_author()) {
		global $wp_query;
		$wp_query->set_404();
		status_header(404);
		nocache_headers();
	}
});
