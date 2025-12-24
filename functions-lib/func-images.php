<?php

declare(strict_types=1);

/**
 * Image helpers
 *
 * - Resolve theme asset URL via `t2025_theme_asset_url()` (dev/prod aware)
 * - Add width/height when possible to reduce CLS
 * - Provide sane defaults for loading/decoding
 */

/**
 * Resolve the filesystem path for a theme image referenced by src path.
 *
 * @param string $srcPath Example: 'src/assets/images/demo/dummy1.jpg'
 */
function t2025_theme_asset_file_path(string $srcPath): string {
	$srcPath = ltrim($srcPath, '/');

	// In dev, the file exists under the theme directory.
	$devCandidate = get_theme_file_path($srcPath);
	if (file_exists($devCandidate)) return $devCandidate;

	// In prod, resolve from theme-assets.json mapping.
	$mapPath = function_exists('t2025_vite_theme_assets_path') ? t2025_vite_theme_assets_path() : '';
	if ($mapPath === '' || !file_exists($mapPath)) return '';

	$raw = file_get_contents($mapPath);
	if ($raw === false) return '';

	$map = json_decode($raw, true);
	if (!is_array($map) || !isset($map[$srcPath]) || !is_string($map[$srcPath])) return '';

	$rel = ltrim($map[$srcPath], '/'); // e.g. assets/images/demo/dummy1-xxxx.jpg
	$distCandidate = get_theme_file_path('dist/' . $rel);
	return file_exists($distCandidate) ? $distCandidate : '';
}

/**
 * Resolve the filesystem path for a theme image under `src/assets/images/**`.
 *
 * @param string $pathUnderImages Example: 'demo/dummy1.jpg'
 */
function t2025_theme_image_file_path(string $pathUnderImages): string {
	$pathUnderImages = ltrim($pathUnderImages, '/');
	return t2025_theme_asset_file_path('src/assets/images/' . $pathUnderImages);
}

/**
 * Build an <img> tag for a theme asset referenced by src path.
 *
 * @param string $srcPath Example: 'src/assets/images/demo/dummy1.jpg'
 * @param array<string, mixed> $attrs Extra attributes (class, loading, decoding, fetchpriority, etc.)
 */
function t2025_img(string $srcPath, string $alt = '', array $attrs = []): string {
	$srcPath = ltrim($srcPath, '/');
	$url = function_exists('t2025_theme_asset_url') ? t2025_theme_asset_url($srcPath) : '';
	if ($url === '') return '';

	$attrs = array_merge([
		'src' => $url,
		'alt' => $alt,
		'loading' => 'lazy',
		'decoding' => 'async',
	], $attrs);

	// Add width/height when possible and not explicitly provided.
	$needsSize = empty($attrs['width']) || empty($attrs['height']);
	if ($needsSize) {
		$path = t2025_theme_asset_file_path($srcPath);
		if ($path !== '') {
			$ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
			$isRaster = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'], true);
			if ($isRaster) {
				$size = @getimagesize($path);
				if (is_array($size) && !empty($size[0]) && !empty($size[1])) {
					if (empty($attrs['width'])) $attrs['width'] = (int) $size[0];
					if (empty($attrs['height'])) $attrs['height'] = (int) $size[1];
				}
			}
		}
	}

	$html = '<img';
	foreach ($attrs as $k => $v) {
		if ($v === null) continue;
		if ($v === false) continue;
		if ($v === '') continue;
		$html .= ' ' . esc_attr((string) $k) . '="' . esc_attr((string) $v) . '"';
	}
	$html .= '>';

	return $html;
}

/**
 * Build an <img> tag for a theme image under `src/assets/images/**`.
 *
 * Example:
 * - t2025_img_image('demo/dummy1.jpg', 'alt')
 */
function t2025_img_image(string $pathUnderImages, string $alt = '', array $attrs = []): string {
	$pathUnderImages = ltrim($pathUnderImages, '/');
	// Resolve URL via the helper if available (keeps logic centralized).
	if (function_exists('t2025_theme_image_url')) {
		$url = t2025_theme_image_url($pathUnderImages);
		if ($url === '') return '';

		$attrs = array_merge([
			'src' => $url,
			'alt' => $alt,
			'loading' => 'lazy',
			'decoding' => 'async',
		], $attrs);

		$needsSize = empty($attrs['width']) || empty($attrs['height']);
		if ($needsSize) {
			$path = t2025_theme_image_file_path($pathUnderImages);
			if ($path !== '') {
				$ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
				$isRaster = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'], true);
				if ($isRaster) {
					$size = @getimagesize($path);
					if (is_array($size) && !empty($size[0]) && !empty($size[1])) {
						if (empty($attrs['width'])) $attrs['width'] = (int) $size[0];
						if (empty($attrs['height'])) $attrs['height'] = (int) $size[1];
					}
				}
			}
		}

		$html = '<img';
		foreach ($attrs as $k => $v) {
			if ($v === null) continue;
			if ($v === false) continue;
			if ($v === '') continue;
			$html .= ' ' . esc_attr((string) $k) . '="' . esc_attr((string) $v) . '"';
		}
		$html .= '>';
		return $html;
	}

	// Fallback: delegate to t2025_img with full path.
	return t2025_img('src/assets/images/' . $pathUnderImages, $alt, $attrs);
}


