<?php

declare(strict_types=1);

/**
 * サムネイル画像表示
 *
 * =========================================
 * 使い方
 * =========================================
 *
 * ▼ 基本（fullサイズ / lazy loading / no-imageあり）
 *   ty_display_thumbnail();
 *
 * ▼ サイズ指定（mediumサイズ / lazy loading）
 *   ty_display_thumbnail('medium');
 *
 * ▼ eager loading（FV・ファーストビュー用）
 *   ty_display_thumbnail('large', 'eager');
 *
 * ▼ サムネイルがない場合に要素ごと出力しない（推奨）
 *   <?php if (has_post_thumbnail()) : ?>
 *     <figure class="p-single__thumbnail">
 *       <?php ty_display_thumbnail('full', 'eager'); ?>
 *     </figure>
 *   <?php endif; ?>
 *   注意: has_post_thumbnail()の判定は呼び出し側で行う。
 *         ty_display_thumbnail()内では判定しない。
 *
 */

function ty_display_thumbnail($size = 'full', $loading = 'lazy', $fallback = true)
{
    $t = ty_get_thumbnail_data($size, $fallback);

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


function ty_get_thumbnail_data($size = 'full', $fallback = true)
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
    // - URL:  ty_theme_image_url()（devはViteサーバー / prodはdistのハッシュ名）
    // - PATH: ty_theme_image_file_path()（devはsrc/配下 / prodはdistを参照）
    $fallback_under_images = 'common/logo.svg';

    $fallback_url = ty_theme_image_url($fallback_under_images);
    $fallback_path = ty_theme_image_file_path($fallback_under_images);

    $dims = ty_get_image_dimensions($fallback_path);

    return [
        'url'    => $fallback_url,
        'width'  => $dims['width'],
        'height' => $dims['height'],
        'class'  => 'u-no-image',
        'alt'    => 'no image',
    ];
}