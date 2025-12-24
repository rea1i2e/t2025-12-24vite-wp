<?php

declare(strict_types=1);

/**
 * Assets (Vite)
 */

if (!defined('T2025_VITE_DEV_SERVER')) {
	define('T2025_VITE_DEV_SERVER', is_ssl() ? 'https://localhost:5173' : 'http://localhost:5173');
}

// Loaded by `functions-lib/loader.php`:
// - func-vite.php
// - func-images.php
// - func-legacy.php
// - func-menu.php

function t2025_enqueue_assets(): void {
	t2025_enqueue_vite_script_entry('src/assets/js/main.js', 't2025');
	t2025_enqueue_vite_style_entry('src/assets/sass/style.scss', 't2025');
}
add_action('wp_enqueue_scripts', 't2025_enqueue_assets');


