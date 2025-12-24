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

if (!function_exists('display_thumbnail')) {
	/**
	 * Echo post thumbnail <img> with width/height/loading.
	 */
	function display_thumbnail(string $size = 'full', string $loading = 'lazy'): void {
		$t = get_thumbnail_data($size);

		$attrs = [
			'src' => esc_url((string) ($t['url'] ?? '')),
			'alt' => esc_attr((string) ($t['alt'] ?? '')),
			'class' => esc_attr((string) ($t['class'] ?? '')),
			'loading' => esc_attr($loading),
		];

		if (!empty($t['width']) && !empty($t['height'])) {
			$attrs['width'] = (int) $t['width'];
			$attrs['height'] = (int) $t['height'];
		}

		$html = '<img';
		foreach ($attrs as $k => $v) {
			if ($v === '') continue;
			$html .= ' ' . $k . '="' . $v . '"';
		}
		$html .= '>';
		echo $html;
	}
}

if (!function_exists('get_thumbnail_data')) {
	function get_thumbnail_data(string $size = 'full'): array {
		if (has_post_thumbnail()) {
			$thumbnail_id = get_post_thumbnail_id();
			$src = wp_get_attachment_image_src($thumbnail_id, $size); // [0]=url, [1]=w, [2]=h
			$url = $src ? $src[0] : wp_get_attachment_url($thumbnail_id);
			$width = $src ? (int) $src[1] : null;
			$height = $src ? (int) $src[2] : null;
			$class = '';
			$alt_text = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
			if ($alt_text === '' || $alt_text === null) {
				$alt_text = get_the_title($thumbnail_id) ?: '';
			}
		} else {
			// Fallback to theme logo (SVG -> omit width/height)
			$url = function_exists('t2025_theme_asset_url') ? t2025_theme_asset_url('src/assets/images/common/logo.svg') : '';
			$width = null;
			$height = null;
			$class = 'u-no-image';
			$alt_text = 'no image';
		}

		return [
			'url' => $url,
			'width' => $width,
			'height' => $height,
			'class' => $class,
			'alt' => $alt_text,
		];
	}
}


