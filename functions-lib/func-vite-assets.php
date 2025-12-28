<?php

declare(strict_types=1);

/**
 * アセット読み込み（Vite）
 * ・開発中: Vite dev serverから読み込み
 * ・本番: 指定したパスをキーに、dist/.vite/manifest.jsonからパスを取得して読み込み
 */

function t2025_enqueue_assets(): void {
	t2025_enqueue_vite_script_entry('src/assets/js/main.js', 't2025');
	t2025_enqueue_vite_style_entry('src/assets/sass/style.scss', 't2025');
}
add_action('wp_enqueue_scripts', 't2025_enqueue_assets');


