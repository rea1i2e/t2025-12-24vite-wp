<?php
declare(strict_types=1);
/**
 * 画像ヘルパー
 *
 * =========================================
 * 使い方（どれを使う？）
 * =========================================
 *
 * ▼ `<img>` を出したい（推奨）
 * - ty_img('demo/dummy1.jpg', 'dummy1.jpg')
 * - ty_img('demo/dummy1.jpg', 'dummy1.jpg', ['loading' => 'eager', 'fetchpriority' => 'high'])
 *
 * ▼ `<picture>` でPC/SPを出し分けしたい
 * - ty_picture_img('demo/dummy3.jpg', 'demo/dummy2.jpg', 'dummy3.jpg')
 *
 * ▼ URLだけ欲しい（src/srcsetなどに入れる）
 *   ※定義は func-vite.php
 * - ty_theme_image_url('demo/dummy1.jpg')
 * - ty_theme_asset_url('src/assets/images/demo/dummy1.jpg') ※定義は func-vite.php
 *
 * 補足:
 * - 可能であれば width/height を自動付与して CLS を抑制します（ラスタ画像のみ）
 * - 画像URLは Vite dev/prod 差を吸収します（theme-assets.json / dev server）
 */

/**
 * src パスで指定されたテーマ画像の「実ファイルパス」を解決する
 *
 * @param string $srcPath 例: 'src/assets/images/demo/dummy1.jpg'
 */
function ty_theme_asset_file_path(string $srcPath): string {
	$srcPath = ltrim($srcPath, '/'); // 冒頭に/があったら除外

	// dev では、テーマ配下（src/）に実ファイルが存在する
	$devCandidate = get_theme_file_path($srcPath);
	if (file_exists($devCandidate)) return $devCandidate;

	// prod では、theme-assets.json のマッピングから dist 側の実ファイルへ解決する
	if (!function_exists('ty_vite_theme_assets_map')) return '';
	$map = ty_vite_theme_assets_map();
	if (!isset($map[$srcPath]) || !is_string($map[$srcPath])) return '';

	$rel = ltrim($map[$srcPath], '/'); // 例: assets/images/demo/dummy1-xxxx.jpg
	$distCandidate = get_theme_file_path('dist/' . $rel);
	return file_exists($distCandidate) ? $distCandidate : '';
}

/**
 * `src/assets/images/**` 配下のテーマ画像の「実ファイルパス」を解決する
 *
 * @param string $pathUnderImages 例: 'demo/dummy1.jpg'
 */
function ty_theme_image_file_path(string $pathUnderImages): string {
	$pathUnderImages = ltrim($pathUnderImages, '/');
	return ty_theme_asset_file_path('src/assets/images/' . $pathUnderImages);
}

// `src/assets/images/**` 配下のテーマ画像から <img> タグを組み立てる（推奨）
function ty_img(string $pathUnderImages, string $alt = '', array $attrs = []): string {
	$pathUnderImages = ltrim($pathUnderImages, '/');
	$url = function_exists('ty_theme_image_url') ? ty_theme_image_url($pathUnderImages) : '';
	if ($url === '') {
		// フォールバック：images配下のsrcパスを直接組み立てる
		$srcPath = 'src/assets/images/' . $pathUnderImages;
		$url = function_exists('ty_theme_asset_url') ? ty_theme_asset_url($srcPath) : '';
	}
	if ($url === '') {
		return '';
	}

	$attrs = array_merge([
		'src' => $url,
		'alt' => $alt,
		'loading' => 'lazy',
		// 'decoding' => 'async',
	], $attrs);

	// width/height が未指定で、取得可能な場合のみ付与する
	$needsSize = empty($attrs['width']) || empty($attrs['height']);
	if ($needsSize) {
		$path = ty_theme_image_file_path($pathUnderImages);
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
		// alt は空でも必ず出力（alt="" を担保）
		if ($v === '' && (string) $k !== 'alt') continue;
		$html .= ' ' . esc_attr((string) $k) . '="' . esc_attr((string) $v) . '"';
	}
	$html .= '>';

	return $html;
}

/**
 * @deprecated 互換用。今後は `ty_img()` を使用してください。
 */
function ty_img_image(string $pathUnderImages, string $alt = '', array $attrs = []): string {
	return ty_img($pathUnderImages, $alt, $attrs);
}

// HTML属性配列を ` key="value"` 形式の文字列へ変換する
function ty_build_html_attrs(array $attrs): string {
	$out = '';
	foreach ($attrs as $k => $v) {
		if ($v === null || $v === false) continue;
		if ($k === '' || !is_string($k)) continue;

		// alt は空でも alt="" を担保（boolean属性扱いにしない）
		if ($k === 'alt' && $v === '') {
			$out .= ' alt=""';
			continue;
		}

		if ($v === true || $v === '') {
			$out .= ' ' . esc_attr($k);
			continue;
		}

		$out .= ' ' . esc_attr($k) . '="' . esc_attr((string) $v) . '"';
	}
	return $out;
}

// PC/SP 画像を <picture> で出し分けして出力する（dev/prod を吸収）
function ty_picture_img(
	string $pcPathUnderImages,
	?string $spPathUnderImages,
	string $alt = '',
	array $imgAttrs = [],
	string $spMedia = '(max-width: 767px)'
): string {
	$pcPathUnderImages = ltrim($pcPathUnderImages, '/');
	$spPathUnderImages = $spPathUnderImages !== null ? ltrim($spPathUnderImages, '/') : null;

	if (!function_exists('ty_theme_image_url') || !function_exists('ty_theme_image_file_path')) {
		return '';
	}

	$pc_url = ty_theme_image_url($pcPathUnderImages);
	$pc_path = ty_theme_image_file_path($pcPathUnderImages);
	if ($pc_url === '' || $pc_path === '') return '';

	$pc_dims = function_exists('ty_get_image_dimensions')
		? ty_get_image_dimensions($pc_path)
		: (function_exists('getimagesize') ? (function () use ($pc_path) {
			$size = @getimagesize($pc_path);
			return (is_array($size) && !empty($size[0]) && !empty($size[1]))
				? ['width' => (int) $size[0], 'height' => (int) $size[1]]
				: ['width' => null, 'height' => null];
		})() : ['width' => null, 'height' => null]);

	$imgAttrs = array_merge([
		'src' => $pc_url,
		'alt' => $alt,
		'loading' => 'lazy',
		// 'decoding' => 'async',
	], $imgAttrs);

	// width/height が未指定なら自動付与（取れた場合のみ）
	if (empty($imgAttrs['width']) && !empty($pc_dims['width'])) $imgAttrs['width'] = (int) $pc_dims['width'];
	if (empty($imgAttrs['height']) && !empty($pc_dims['height'])) $imgAttrs['height'] = (int) $pc_dims['height'];

	$source_html = '';
	if ($spPathUnderImages !== null && $spPathUnderImages !== '') {
		$sp_url = ty_theme_image_url($spPathUnderImages);
		$sp_path = ty_theme_image_file_path($spPathUnderImages);

		if ($sp_url !== '' && $sp_path !== '') {
			$sp_dims = function_exists('ty_get_image_dimensions')
				? ty_get_image_dimensions($sp_path)
				: (function_exists('getimagesize') ? (function () use ($sp_path) {
					$size = @getimagesize($sp_path);
					return (is_array($size) && !empty($size[0]) && !empty($size[1]))
						? ['width' => (int) $size[0], 'height' => (int) $size[1]]
						: ['width' => null, 'height' => null];
				})() : ['width' => null, 'height' => null]);

			$source_attrs = [
				'srcset' => $sp_url,
				'media' => $spMedia,
			];
			// 要望に合わせて <source> にも付与（不要なら削ってOK）
			if (!empty($sp_dims['width'])) $source_attrs['width'] = (int) $sp_dims['width'];
			if (!empty($sp_dims['height'])) $source_attrs['height'] = (int) $sp_dims['height'];

			$source_html = '<source' . ty_build_html_attrs($source_attrs) . '>';
		}
	}

	return '<picture>'
		. $source_html
		. '<img' . ty_build_html_attrs($imgAttrs) . '>'
		. '</picture>';
}


