<?php

declare(strict_types=1);

/**
 * サムネイル画像表示関数
 * 
 * 使用方法:
 * 
 * // デフォルト（fullサイズ、lazy loading）
 * <?php display_thumbnail(); ?>
 * 
 * // eager loadingを指定
 * <?php display_thumbnail('full', 'eager'); ?>
 * 
 * // サイズとloadingを指定
 * <?php display_thumbnail('medium', 'eager'); ?>
 * 
 * @param string $size 画像サイズ（デフォルト: 'full'）
 * @param string $loading loading属性（'lazy' または 'eager'、デフォルト: 'lazy'）
 */

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


