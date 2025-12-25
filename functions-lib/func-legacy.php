<?php

declare(strict_types=1);

/**
 * Legacy helper functions migrated from the old theme (t_2025-01-11wp).
 *
 * Notes:
 * - Keep function names to minimize template changes.
 * - Asset URLs are resolved via `t2025_theme_asset_url()` (Vite dev/prod aware).
 */

if (!function_exists('temp_path')) {
	/**
	 * Echo theme file URL (theme root relative).
	 * Kept for compatibility with old templates.
	 */
	function temp_path(string $file = ''): void {
		echo esc_url(get_theme_file_uri($file));
	}
}

if (!function_exists('assets_path')) {
	/**
	 * Echo legacy assets path (not used in the Vite theme; kept for compatibility).
	 */
	function assets_path(string $file = ''): void {
		echo esc_url(get_theme_file_uri('/assets' . $file));
	}
}

if (!function_exists('img_path')) {
	/**
	 * Echo theme image URL.
	 * Old theme used `/assets/images/...`; this theme resolves from `src/assets/images/...`.
	 */
	function img_path(string $file = ''): void {
		$file = ltrim($file, '/'); // e.g. 'common/logo.svg'
		$url = function_exists('t2025_theme_image_url') ? t2025_theme_image_url($file) : '';
		echo esc_url($url);
	}
}

if (!function_exists('uploads_path')) {
	function uploads_path(): void {
		echo esc_url(wp_upload_dir()['baseurl']);
	}
}

if (!function_exists('page_path')) {
	/**
	 * Echo home URL with a normalized trailing slash for slugs.
	 */
	function page_path(string $page = ''): void {
		// スラッシュが不要な場合は処理しない
		if (
			strpos($page, '#') === false &&
			strpos($page, '?') === false &&
			!preg_match('/\.[a-zA-Z0-9]+$/', $page)
		) {
			$page .= '/';
		}
		echo esc_url(home_url($page));
	}
}

if (!function_exists('get_page_path')) {
	function get_page_path(string $page = ''): string {
		// スラッシュが不要な場合は処理しない
		if (
			strpos($page, '#') === false &&
			strpos($page, '?') === false &&
			!preg_match('/\.[a-zA-Z0-9]+$/', $page)
		) {
			$page .= '/';
		}
		return esc_url(home_url($page));
	}
}

// get_nav_items() is defined in `functions-lib/func-nav-items.php` (legacy-compatible).

// display_thumbnail() / get_thumbnail_data() は `functions-lib/func-thumbnail.php` に分離


