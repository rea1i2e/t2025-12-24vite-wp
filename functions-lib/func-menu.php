<?php

declare(strict_types=1);

/**
 * Menu helpers (legacy: `get_nav_items()` as the single source of truth)
 */

/**
 * Return <li>...</li> HTML from `get_nav_items()`.
 *
 * Note: `$location` is kept for backward compatibility with previous calls,
 * but is intentionally ignored.
 */
function t2025_menu_items_html(
	string $location,
	string $itemClass,
	string $linkClass,
	bool $allowLegacyFallback = true
): string {
	if (!$allowLegacyFallback) return '';

	if (!function_exists('get_nav_items')) return '';
	$items = get_nav_items();
	if (!is_array($items)) return '';

	$html = '';
	foreach ($items as $item) {
		if (!is_array($item)) continue;
		$slug = isset($item['slug']) ? (string) $item['slug'] : '';
		$text = isset($item['text']) ? (string) $item['text'] : '';
		if ($slug === '' || $text === '') continue;

		$is_current = '';
		if (is_front_page() && $slug === 'top') {
			$is_current = 'is-current';
		} elseif (is_page($slug) || is_post_type_archive($slug) || is_category($slug) || is_tax($slug)) {
			$is_current = 'is-current';
		}

		$modifier = isset($item['modifier']) ? (string) $item['modifier'] : '';
		$li_class = $itemClass
			. ($modifier !== '' ? ' ' . $itemClass . '--' . $modifier : '')
			. ($is_current !== '' ? ' ' . $is_current : '');

		$url = ($slug === 'top') ? home_url('/') : home_url('/' . trim($slug, '/') . '/');

		$html .= '<li class="' . esc_attr($li_class) . '">';
		$html .= '<a class="' . esc_attr($linkClass) . '" href="' . esc_url($url) . '">';
		$html .= esc_html($text);
		$html .= '</a></li>';
	}

	return $html;
}


