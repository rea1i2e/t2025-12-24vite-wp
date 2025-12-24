<?php

declare(strict_types=1);

/**
 * Theme setup
 */
function t2025_setup(): void {
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'script',
		'style',
	]);

	register_nav_menus([
		'global' => 'Global Navigation',
		'footer' => 'Footer Navigation',
	]);
}
add_action('after_setup_theme', 't2025_setup');

/**
 * Assets (Vite)
 */
if (!defined('T2025_VITE_DEV_SERVER')) {
	define('T2025_VITE_DEV_SERVER', is_ssl() ? 'https://localhost:5173' : 'http://localhost:5173');
}
require_once get_theme_file_path('/inc/vite.php');

function t2025_enqueue_assets(): void {
	t2025_enqueue_vite_script_entry('src/assets/js/main.js', 't2025');
	t2025_enqueue_vite_style_entry('src/assets/sass/style.scss', 't2025');
}
add_action('wp_enqueue_scripts', 't2025_enqueue_assets');

/**
 * Ensure module scripts are output with type="module" (especially in dev).
 */
function t2025_script_loader_tag(string $tag, string $handle, string $src): string {
	if (strpos($handle, 't2025-') !== 0) return $tag;
	if (strpos($tag, ' type=') !== false) return $tag;
	return str_replace('<script ', '<script type="module" ', $tag);
}
add_filter('script_loader_tag', 't2025_script_loader_tag', 10, 3);


