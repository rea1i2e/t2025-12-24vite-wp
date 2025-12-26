<?php

/**
 * サムネイル画像表示
 *
 * =========================================
 * 使い方
 * =========================================
 *
 * ▼ 基本（fullサイズ / lazy loading / no-imageあり）
 *   display_thumbnail();
 *
 * ▼ サイズ指定（mediumサイズ / lazy loading）
 *   display_thumbnail('medium');
 *
 * ▼ eager loading（FV・ファーストビュー用）
 *   display_thumbnail('large', 'eager');
 *
 * ▼ サムネイルが無い場合は何も出力しない
 *   display_thumbnail('medium', 'lazy', false);
 *
 */



function display_thumbnail($size = 'full', $loading = 'lazy', $fallback = true)
{
    $t = get_thumbnail_data($size, $fallback);

    // サムネイルもフォールバックも無い場合は何も出さない
    if (!$t) {
        return;
    }

    // loading 属性のバリデーション
    $loading = ($loading === 'eager') ? 'eager' : 'lazy';

    $attrs = [
        'src'     => esc_url($t['url']),
        'alt'     => esc_attr($t['alt']),
        'class'   => esc_attr($t['class']),
        'loading' => esc_attr($loading),
    ];

    // width / height がある場合のみ出力
    if (!empty($t['width']) && !empty($t['height'])) {
        $attrs['width']  = (int) $t['width'];
        $attrs['height'] = (int) $t['height'];
    }

    $html = '<img';
    foreach ($attrs as $k => $v) {
        if ($v === '') continue; // 空属性は出さない（任意）
        $html .= ' ' . $k . '="' . $v . '"';
    }
    $html .= '>';
    echo $html;
}


function get_thumbnail_data($size = 'full', $fallback = true)
{
    if (has_post_thumbnail()) {
        $thumbnail_id = get_post_thumbnail_id();
        $src          = wp_get_attachment_image_src($thumbnail_id, $size);

        $url    = $src ? $src[0] : wp_get_attachment_url($thumbnail_id);
        $width  = $src ? (int) $src[1] : null;
        $height = $src ? (int) $src[2] : null;
        $class  = '';

        $alt_text = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
        if ($alt_text === '' || $alt_text === null) {
            $alt_text = get_the_title() ?: '';
        }

        return [
            'url'    => $url,
            'width'  => $width,
            'height' => $height,
            'class'  => $class,
            'alt'    => $alt_text,
        ];
    }

    // フォールバックを出さない場合
    if (!$fallback) {
        return null;
    }

    // フォールバック画像（dev/prod でパスが変わるため、可能ならヘルパー経由で解決する）
    // - URL:  t2025_theme_image_url()（devはViteサーバー / prodはdistのハッシュ名）
    // - PATH: t2025_theme_image_file_path()（devはsrc/配下 / prodはdistを参照）
    $fallback_under_images = 'common/logo.svg';

    $fallback_url = function_exists('t2025_theme_image_url')
        ? t2025_theme_image_url($fallback_under_images)
        : get_template_directory_uri() . '/src/assets/images/' . $fallback_under_images;

    $fallback_path = function_exists('t2025_theme_image_file_path')
        ? t2025_theme_image_file_path($fallback_under_images)
        : get_template_directory() . '/src/assets/images/' . $fallback_under_images;

    $dims = t2025_get_image_dimensions($fallback_path);

    return [
        'url'    => $fallback_url,
        'width'  => $dims['width'],
        'height' => $dims['height'],
        'class'  => 'u-no-image',
        'alt'    => 'no image',
    ];
}

/**
 * 画像ファイルから width/height を取得する
 *
 * - ラスタ（png/jpg/webp等）は `getimagesize()` を使用
 * - svg は width/height または viewBox から推定
 *
 * @return array{width:int|null,height:int|null}
 */
function t2025_get_image_dimensions($path)
{
    if (!is_string($path) || $path === '' || !is_readable($path)) {
        return ['width' => null, 'height' => null];
    }

    $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

    // Raster images
    if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'avif'], true)) {
        $size = @getimagesize($path);
        if (is_array($size) && !empty($size[0]) && !empty($size[1])) {
            return [
                'width' => (int) $size[0],
                'height' => (int) $size[1],
            ];
        }
        return ['width' => null, 'height' => null];
    }

    // SVG
    if ($ext === 'svg') {
        $svg = file_get_contents($path);
        if (!is_string($svg) || $svg === '') {
            return ['width' => null, 'height' => null];
        }

        $width = null;
        $height = null;

        // width="371" / width="371px" のような形式に対応
        if (preg_match('/\bwidth="([\d.]+)(px)?"/i', $svg, $m)) {
            $width = (int) floor((float) $m[1]);
        }
        if (preg_match('/\bheight="([\d.]+)(px)?"/i', $svg, $m)) {
            $height = (int) floor((float) $m[1]);
        }

        // viewBox="0 0 W H" から拾う
        if ((!$width || !$height) && preg_match('/\bviewBox="[\d.\-]+\s+[\d.\-]+\s+([\d.]+)\s+([\d.]+)"/i', $svg, $m)) {
            if (!$width) $width = (int) floor((float) $m[1]);
            if (!$height) $height = (int) floor((float) $m[2]);
        }

        return ['width' => $width, 'height' => $height];
    }

    return ['width' => null, 'height' => null];
}