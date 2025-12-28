<?php

declare(strict_types=1);

/**
 * アセット読み込み（Vite）
 * ・開発中: Vite dev serverから読み込み
 * ・本番: 指定したパスをキーに、dist/.vite/manifest.jsonからパスを取得して読み込み
 */

function ty_enqueue_assets(): void {
	ty_enqueue_vite_script_entry('src/assets/js/main.js', 't2025');
	ty_enqueue_vite_style_entry('src/assets/sass/style.scss', 't2025');
}
add_action('wp_enqueue_scripts', 'ty_enqueue_assets');


