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
	$rel = ty_vite_theme_asset_dist_rel($srcPath);
	if ($rel === '') return '';

	$rel = ltrim($rel, '/'); // 例: assets/images/demo/dummy1-xxxx.jpg
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
 * `src/assets/images/**` 配下のテーマ画像から <img> タグの HTML 文字列を返す（取得のみ）
 *
 * @param string $pathUnderImages images 配下の相対パス（例: 'demo/dummy1.jpg'）
 * @param string $alt alt 属性
 * @param bool   $eager true のとき loading="eager"、省略時は loading="lazy"
 * @param string $extraAttrs その他の属性を文字列で（例: 'class="hero" fetchpriority="high"'）。動的値は呼び出し側で esc_attr すること
 */
function ty_get_img(string $pathUnderImages, string $alt = '', bool $eager = false, string $extraAttrs = ''): string {
	$pathUnderImages = ltrim($pathUnderImages, '/');
	$url = ty_theme_image_url($pathUnderImages);
	if ($url === '') {
		// フォールバック：images配下のsrcパスを直接組み立てる
		$srcPath = 'src/assets/images/' . $pathUnderImages;
		$url = ty_theme_asset_url($srcPath);
	}
	if ($url === '') {
		return '';
	}

	$attrs = [
		'src' => $url,
		'alt' => $alt,
		'loading' => $eager ? 'eager' : 'lazy',
	];

	// width/height が取得可能な場合のみ付与する（JSON の寸法を優先し、無い場合のみ getimagesize にフォールバック）
	$dims = ty_theme_image_dimensions($pathUnderImages);
	if ($dims['width'] !== null && $dims['height'] !== null) {
		$attrs['width'] = $dims['width'];
		$attrs['height'] = $dims['height'];
	} else {
		$path = ty_theme_image_file_path($pathUnderImages);
		if ($path !== '') {
			$ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
			$isRaster = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'], true);
			if ($isRaster) {
				$size = @getimagesize($path);
				if (is_array($size) && !empty($size[0]) && !empty($size[1])) {
					$attrs['width'] = (int) $size[0];
					$attrs['height'] = (int) $size[1];
				}
			}
		}
	}

	$html = '<img';
	foreach ($attrs as $k => $v) {
		if ($v === null || $v === false) continue;
		// alt は空でも必ず出力（alt="" を担保）
		if ($v === '' && (string) $k !== 'alt') continue;
		$html .= ' ' . esc_attr((string) $k) . '="' . esc_attr((string) $v) . '"';
	}
	if ($extraAttrs !== '') {
		$html .= ' ' . trim($extraAttrs);
	}
	$html .= '>';

	return $html;
}

/**
 * ty_get_img() の結果をそのまま出力する。テンプレートではこちらを主に使用。
 */
function ty_img(string $pathUnderImages, string $alt = '', bool $eager = false, string $extraAttrs = ''): void {
	echo ty_get_img($pathUnderImages, $alt, $eager, $extraAttrs);
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

/**
 * PC/SP 画像を <picture> で出し分けした HTML 文字列を返す（取得のみ）
 * SP 用パスは null/空のとき PC パスから自動導出（name.png → name_sp.png）。
 *
 * @param string      $pcPathUnderImages PC用画像の images 配下パス
 * @param string|null $spPathOrAlt       SP用画像の images 配下パス（null/空なら自動導出）
 * @param string      $alt              alt 属性
 * @param bool        $eager             true のとき loading="eager"
 * @param string      $extraAttrs       <img> へのその他属性を文字列で（例: 'fetchpriority="high"'）
 * @param string      $spMedia          <source> の media 値
 */
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

		$source_html = '<source' . ty_build_html_attrs($source_attrs) . '>';
	}

	$imgTag = '<img' . ty_build_html_attrs($imgAttrs);
	if ($extraAttrs !== '') {
		$imgTag .= ' ' . trim($extraAttrs);
	}
	$imgTag .= '>';

	return '<picture>' . $source_html . $imgTag . '</picture>';
}

/**
 * ty_get_picture_img() の結果をそのまま出力する。テンプレートではこちらを主に使用。
 * 2引数で呼んだときは (pcPath, alt)、3引数以上は (pcPath, spPath, alt, ...)。
 */
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

