<?php

declare(strict_types=1);

/**
 * アセット読み込み（Vite）
 * ・開発中: Vite dev serverから読み込み
 * ・本番: 指定したパスをキーに、dist/.vite/manifest.jsonからパスを取得して読み込み
 * ※開発中、本番の判定とパスの解決はfunc-vite.phpで行う
 */

function ty_enqueue_assets(): void {
	ty_enqueue_vite_script_entry('src/assets/js/main.js', 'ty');
	ty_enqueue_vite_style_entry('src/assets/sass/style.scss', 'ty');
}
add_action('wp_enqueue_scripts', 'ty_enqueue_assets');

// ES modules 用に type="module" を自動付与（保険として）
function ty_script_loader_tag(string $tag, string $handle, string $src): string {
	if (strpos($handle, 'ty-') !== 0) return $tag;
	if (strpos($tag, ' type=') !== false) return $tag;
	return str_replace('<script ', '<script type="module" ', $tag);
}
add_filter('script_loader_tag', 'ty_script_loader_tag', 10, 3);


