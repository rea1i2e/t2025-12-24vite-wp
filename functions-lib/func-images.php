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
 * - ty_img('demo/dummy1.jpg', 'altテキスト')  … そのまま出力（echo 不要）
 * - ty_img('demo/dummy1.jpg', 'altテキスト', true)  … loading="eager"（LCP用など）
 * - ty_img('demo/dummy1.jpg', 'altテキスト', false, 'class="hero" fetchpriority="high"')
 * ▼ 取得のみ（変数に入れる・連結する等）は ty_get_img()
 *
 * ▼ `<picture>` でPC/SPを出し分けしたい（省略時は命名規則で SP を導出: name.png → name_sp.png）
 * - ty_picture_img('demo/name1.png', 'altテキスト')
 * - SP の拡張子が異なる場合: ty_picture_img('demo/name1.png', 'demo/name1_sp.svg', 'altテキスト')
 * - 第4引数 true で loading="eager"、第5引数でその他属性を文字列で
 * ▼ 取得のみは ty_get_picture_img()
 *
 * ▼ URL だけ欲しい（src・srcset 用など）
 *   ty_theme_image_url('demo/dummy1.jpg') / ty_theme_asset_url('src/assets/images/...')（定義は func-vite.php）
 *
 * 補足:
 * - 可能であれば width/height を自動付与して CLS を抑制します。寸法はビルド時に theme-assets.json に含まれる場合はそれを参照し、無い場合（未ビルド・開発時など）のみ getimagesize で取得します
 * - 画像URLは Vite dev/prod 差を吸収します（theme-assets.json / dev server）
 * - 本番ビルドで config/theme-build.config.js の imageAltFormats が avif/webp/both のとき、ラスタ画像は `<picture>` に AVIF/WebP の `<source type="...">` を付与。WP テンプレ既定は none（プラグイン想定）
 */
/**
 * ビルド設定の imageAltFormats（none | webp | avif | both）。dev では none。
 */
function ty_image_alt_formats(): string
{
	if (ty_vite_is_dev()) {
		return 'none';
	}
	$cfg = ty_vite_theme_build_config();
	if (isset($cfg['imageAltFormats']) && is_string($cfg['imageAltFormats'])) {
		return $cfg['imageAltFormats'];
	}
	return 'none';
}

/**
 * @return list<string> 挿入するフォーマット（AVIF → WebP の順）
 */
function ty_image_alt_formats_inject_order(): array
{
	$mode = ty_image_alt_formats();
	if ($mode === 'none') {
		return [];
	}
	if ($mode === 'webp') {
		return ['webp'];
	}
	if ($mode === 'avif') {
		return ['avif'];
	}
	if ($mode === 'both') {
		return ['avif', 'webp'];
	}
	return [];
}

/**
 * dist 相対パスから代替フォーマットの候補パス一覧（after-build candidates 相当）
 *
 * @return list<string>
 */
function ty_theme_image_variant_dist_rel_candidates(string $distRel, string $formatExt): array
{
	$distRel = ltrim($distRel, '/');
	if (!preg_match('/\.(jpe?g|png|gif)$/i', $distRel)) {
		return [];
	}
	$baseNoExt = (string) preg_replace('/\.(jpe?g|png|gif)$/i', '', $distRel);
	$origExt = '';
	if (preg_match('/\.(jpe?g|png|gif)$/i', $distRel, $m)) {
		$origExt = $m[0];
	}
	$out = [$baseNoExt . '.' . $formatExt];
	if ($origExt !== '') {
		$out[] = str_replace($origExt, $origExt . '.' . $formatExt, $distRel);
	}
	return array_values(array_unique($out));
}

/**
 * テーマ画像の代替フォーマット URL（prod のみ。存在しない場合は空文字）
 */
function ty_theme_image_variant_url(string $pathUnderImages, string $formatExt): string
{
	if (ty_vite_is_dev()) {
		return '';
	}
	if (!in_array($formatExt, ['webp', 'avif'], true)) {
		return '';
	}

	$srcKey = 'src/assets/images/' . ltrim($pathUnderImages, '/');
	$distRel = ty_vite_theme_asset_dist_rel($srcKey);
	if ($distRel === '') {
		return '';
	}

	foreach (ty_theme_image_variant_dist_rel_candidates($distRel, $formatExt) as $cand) {
		$abs = ty_vite_dist_path() . '/' . ltrim($cand, '/');
		if (file_exists($abs)) {
			return ty_vite_dist_url() . '/' . ltrim($cand, '/');
		}
	}
	return '';
}

/**
 * ラスター画像パスか（jpg/png/gif）
 */
function ty_is_raster_image_path(string $pathUnderImages): bool
{
	return (bool) preg_match('/\.(jpe?g|png|gif)$/i', $pathUnderImages);
}

/**
 * extraAttrs に data-no-picture が含まれるか
 */
function ty_extra_attrs_has_no_picture(string $extraAttrs): bool
{
	return (bool) preg_match('/\bdata-no-picture\b/i', $extraAttrs);
}

/**
 * format 用 <source> タグ群（media 省略＝フォーマットのみ）
 */
function ty_build_format_source_tags(string $pathUnderImages, string $media = ''): string
{
	if (!ty_is_raster_image_path($pathUnderImages)) {
		return '';
	}

	$dims = ty_theme_image_dimensions($pathUnderImages);
	$html = '';

	foreach (ty_image_alt_formats_inject_order() as $fmt) {
		$url = ty_theme_image_variant_url($pathUnderImages, $fmt);
		if ($url === '') {
			continue;
		}
		$type = $fmt === 'avif' ? 'image/avif' : 'image/webp';
		$attrs = [
			'srcset' => $url,
			'type' => $type,
		];
		if ($media !== '') {
			$attrs['media'] = $media;
		}
		if (!empty($dims['width'])) {
			$attrs['width'] = (int) $dims['width'];
		}
		if (!empty($dims['height'])) {
			$attrs['height'] = (int) $dims['height'];
		}
		$html .= '<source' . ty_build_html_attrs($attrs) . '>';
	}

	return $html;
}

/**
 * 実ファイルパスから width/height を取得
 *
 * @return array{width: int|null, height: int|null}
 */
function ty_get_image_dimensions(string $absPath): array
{
	if ($absPath === '' || !file_exists($absPath)) {
		return ['width' => null, 'height' => null];
	}
	$ext = strtolower((string) pathinfo($absPath, PATHINFO_EXTENSION));
	$isRaster = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'], true);
	if (!$isRaster) {
		return ['width' => null, 'height' => null];
	}
	$size = @getimagesize($absPath);
	if (is_array($size) && !empty($size[0]) && !empty($size[1])) {
		return ['width' => (int) $size[0], 'height' => (int) $size[1]];
	}
	return ['width' => null, 'height' => null];
}

/**
 * src パスで指定されたテーマ画像の「実ファイルパス」を解決する
 *
 * @param string $srcPath 例: 'src/assets/images/demo/dummy1.jpg'
 */
function ty_theme_asset_file_path(string $srcPath): string {
	$srcPath = ltrim($srcPath, '/');

	$devCandidate = get_theme_file_path($srcPath);
	if (file_exists($devCandidate)) return $devCandidate;

	$rel = ty_vite_theme_asset_dist_rel($srcPath);
	if ($rel === '') return '';

	$rel = ltrim($rel, '/');
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

/**
 * PC用画像パスから SP 用パスを導出する（命名規則: name.png → name_sp.png）
 */
function ty_theme_image_sp_path(string $pcPathUnderImages): string {
	$pcPathUnderImages = ltrim($pcPathUnderImages, '/');
	$dir = pathinfo($pcPathUnderImages, PATHINFO_DIRNAME);
	$filename = pathinfo($pcPathUnderImages, PATHINFO_FILENAME);
	$ext = pathinfo($pcPathUnderImages, PATHINFO_EXTENSION);
	$base = ($dir !== '' && $dir !== '.') ? $dir . '/' . $filename : $filename;
	return $base . '_sp.' . $ext;
}

/**
 * `<img>` 用の属性配列を組み立てる
 *
 * @return array<string, scalar>
 */
function ty_build_img_attrs_array(
	string $pathUnderImages,
	string $alt,
	bool $eager
): array {
	$pathUnderImages = ltrim($pathUnderImages, '/');
	$url = ty_theme_image_url($pathUnderImages);
	if ($url === '') {
		$srcPath = 'src/assets/images/' . $pathUnderImages;
		$url = ty_theme_asset_url($srcPath);
	}
	if ($url === '') {
		return [];
	}

	$attrs = [
		'src' => $url,
		'alt' => $alt,
		'loading' => $eager ? 'eager' : 'lazy',
	];

	$dims = ty_theme_image_dimensions($pathUnderImages);
	if ($dims['width'] !== null && $dims['height'] !== null) {
		$attrs['width'] = $dims['width'];
		$attrs['height'] = $dims['height'];
	} else {
		$filePath = ty_theme_image_file_path($pathUnderImages);
		if ($filePath !== '') {
			$sizeDims = ty_get_image_dimensions($filePath);
			if ($sizeDims['width'] !== null && $sizeDims['height'] !== null) {
				$attrs['width'] = $sizeDims['width'];
				$attrs['height'] = $sizeDims['height'];
			}
		}
	}

	return $attrs;
}

/**
 * `src/assets/images/**` 配下のテーマ画像から <img> タグの HTML 文字列を返す（取得のみ）
 */
function ty_get_img(string $pathUnderImages, string $alt = '', bool $eager = false, string $extraAttrs = ''): string {
	$attrs = ty_build_img_attrs_array($pathUnderImages, $alt, $eager);
	if ($attrs === []) {
		return '';
	}

	$imgTag = '<img' . ty_build_html_attrs($attrs);
	if ($extraAttrs !== '') {
		$imgTag .= ' ' . trim($extraAttrs);
	}
	$imgTag .= '>';

	$useFormatPicture = ty_is_raster_image_path($pathUnderImages)
		&& !ty_extra_attrs_has_no_picture($extraAttrs)
		&& ty_image_alt_formats_inject_order() !== []
		&& ty_build_format_source_tags($pathUnderImages) !== '';

	if (!$useFormatPicture) {
		return $imgTag;
	}

	return '<picture>' . ty_build_format_source_tags($pathUnderImages) . $imgTag . '</picture>';
}

/**
 * ty_get_img() の結果をそのまま出力する。テンプレートではこちらを主に使用。
 */
function ty_img(string $pathUnderImages, string $alt = '', bool $eager = false, string $extraAttrs = ''): void {
	echo ty_get_img($pathUnderImages, $alt, $eager, $extraAttrs);
}

function ty_build_html_attrs(array $attrs): string {
	$out = '';
	foreach ($attrs as $k => $v) {
		if ($v === null || $v === false) continue;
		if ($k === '' || !is_string($k)) continue;

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

function ty_get_picture_img(
	string $pcPathUnderImages,
	?string $spPathOrAlt = null,
	string $alt = '',
	bool $eager = false,
	string $extraAttrs = '',
	string $spMedia = '(max-width: 767px)'
): string {
	$pcPathUnderImages = ltrim($pcPathUnderImages, '/');
	$spPathUnderImages = ($spPathOrAlt !== null && $spPathOrAlt !== '')
		? ltrim($spPathOrAlt, '/')
		: ty_theme_image_sp_path($pcPathUnderImages);

	$pc_url = ty_theme_image_url($pcPathUnderImages);
	if ($pc_url === '') return '';

	$pc_dims = ty_theme_image_dimensions($pcPathUnderImages);
	if ($pc_dims['width'] === null || $pc_dims['height'] === null) {
		$pc_path = ty_theme_image_file_path($pcPathUnderImages);
		if ($pc_path !== '') {
			$pc_dims = ty_get_image_dimensions($pc_path);
		}
	}

	$imgAttrs = [
		'src' => $pc_url,
		'alt' => $alt,
		'loading' => $eager ? 'eager' : 'lazy',
	];
	if (!empty($pc_dims['width'])) $imgAttrs['width'] = (int) $pc_dims['width'];
	if (!empty($pc_dims['height'])) $imgAttrs['height'] = (int) $pc_dims['height'];

	$skipFormat = ty_extra_attrs_has_no_picture($extraAttrs);
	$format_html = $skipFormat ? '' : ty_build_format_source_tags($pcPathUnderImages);

	$source_html = '';
	$sp_url = ty_theme_image_url($spPathUnderImages);
	if ($sp_url !== '') {
		$sp_dims = ty_theme_image_dimensions($spPathUnderImages);
		if ($sp_dims['width'] === null || $sp_dims['height'] === null) {
			$sp_path = ty_theme_image_file_path($spPathUnderImages);
			if ($sp_path !== '') {
				$sp_dims = ty_get_image_dimensions($sp_path);
			}
		}

		$source_attrs = [
			'srcset' => $sp_url,
			'media' => $spMedia,
		];
		if (!empty($sp_dims['width'])) $source_attrs['width'] = (int) $sp_dims['width'];
		if (!empty($sp_dims['height'])) $source_attrs['height'] = (int) $sp_dims['height'];

		if (!$skipFormat && ty_is_raster_image_path($spPathUnderImages)) {
			$source_html .= ty_build_format_source_tags($spPathUnderImages, $spMedia);
		}

		$source_html .= '<source' . ty_build_html_attrs($source_attrs) . '>';
	}

	$imgTag = '<img' . ty_build_html_attrs($imgAttrs);
	if ($extraAttrs !== '') {
		$imgTag .= ' ' . trim($extraAttrs);
	}
	$imgTag .= '>';

	return '<picture>' . $format_html . $source_html . $imgTag . '</picture>';
}

function ty_picture_img(
	string $pcPathUnderImages,
	?string $spPathOrAlt = null,
	string $alt = '',
	bool $eager = false,
	string $extraAttrs = '',
	string $spMedia = '(max-width: 767px)'
): void {
	if (func_num_args() < 3) {
		echo ty_get_picture_img($pcPathUnderImages, null, (string) $spPathOrAlt, $eager, $extraAttrs, $spMedia);
	} else {
		echo ty_get_picture_img($pcPathUnderImages, $spPathOrAlt, $alt, $eager, $extraAttrs, $spMedia);
	}
}
