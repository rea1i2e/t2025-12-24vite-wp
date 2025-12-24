<?php

declare(strict_types=1);

/**
 * Vite helper for WordPress enqueue
 *
 * - Dev: load from Vite dev server (HMR)
 * - Prod: load from dist/manifest.json
 */

function t2025_vite_is_dev(): bool {
	$dev_server = defined('T2025_VITE_DEV_SERVER') ? (string) T2025_VITE_DEV_SERVER : '';
	if ($dev_server === '') return false;

	$health_url = rtrim($dev_server, '/') . '/@vite/client';
	$response = wp_remote_head($health_url, ['timeout' => 0.25, 'sslverify' => false]);
	return !is_wp_error($response) && (int) wp_remote_retrieve_response_code($response) >= 200;
}

function t2025_vite_dev_server(): string {
	$dev_server = defined('T2025_VITE_DEV_SERVER') ? (string) T2025_VITE_DEV_SERVER : 'http://localhost:5173';
	return rtrim($dev_server, '/');
}

function t2025_vite_dist_url(): string {
	return rtrim(get_stylesheet_directory_uri(), '/') . '/dist';
}

function t2025_vite_dist_path(): string {
	return rtrim(get_stylesheet_directory(), '/') . '/dist';
}

function t2025_vite_manifest_path(): string {
	// Vite outputs manifest under dist/.vite/manifest.json by default.
	$path = t2025_vite_dist_path() . '/.vite/manifest.json';
	if (file_exists($path)) return $path;

	// Fallback for older configs that output dist/manifest.json
	return t2025_vite_dist_path() . '/manifest.json';
}

function t2025_vite_theme_assets_path(): string {
	return t2025_vite_dist_path() . '/theme-assets.json';
}

/**
 * Resolve a theme asset URL.
 *
 * Intended for <img src="..."> where PHP needs to know the built file name.
 *
 * @param string $srcPath Example: 'src/assets/images/demo/dummy1.jpg'
 */
function t2025_theme_asset_url(string $srcPath): string {
	$srcPath = ltrim($srcPath, '/');

	if (t2025_vite_is_dev()) {
		return t2025_vite_dev_server() . '/' . $srcPath;
	}

	$mapPath = t2025_vite_theme_assets_path();
	if (!file_exists($mapPath)) {
		return '';
	}

	$raw = file_get_contents($mapPath);
	if ($raw === false) return '';

	$map = json_decode($raw, true);
	if (!is_array($map) || !isset($map[$srcPath]) || !is_string($map[$srcPath])) {
		return '';
	}

	return t2025_vite_dist_url() . '/' . ltrim($map[$srcPath], '/');
}

/**
 * Enqueue Vite client (dev only)
 *
 * @param string $handle Example: 't2025'
 */
function t2025_enqueue_vite_client(string $handle = 't2025'): void {
	if (!t2025_vite_is_dev()) return;

	$dev = t2025_vite_dev_server();
	wp_enqueue_script(
		$handle . '-vite-client',
		$dev . '/@vite/client',
		[],
		null,
		['in_footer' => false]
	);
	wp_script_add_data($handle . '-vite-client', 'type', 'module');
}

/**
 * Enqueue Vite module script entry (dev/prod)
 *
 * @param string $entry  Example: 'src/assets/js/main.js'
 * @param string $handle Example: 't2025'
 */
function t2025_enqueue_vite_script_entry(string $entry, string $handle = 't2025'): void {
	if (t2025_vite_is_dev()) {
		t2025_enqueue_vite_client($handle);
		$dev = t2025_vite_dev_server();

		wp_enqueue_script(
			$handle . '-vite-entry',
			$dev . '/' . ltrim($entry, '/'),
			[],
			null,
			['in_footer' => false]
		);
		wp_script_add_data($handle . '-vite-entry', 'type', 'module');
		return;
	}

	$manifest_path = t2025_vite_manifest_path();
	if (!file_exists($manifest_path)) {
		return;
	}

	$manifest_raw = file_get_contents($manifest_path);
	if ($manifest_raw === false) return;

	$manifest = json_decode($manifest_raw, true);
	if (!is_array($manifest) || !isset($manifest[$entry]) || !is_array($manifest[$entry])) {
		return;
	}

	$dist_url = t2025_vite_dist_url();
	$entry_data = $manifest[$entry];

	// CSS imported from the JS entry (e.g. library css)
	if (isset($entry_data['css']) && is_array($entry_data['css'])) {
		foreach ($entry_data['css'] as $i => $css_file) {
			if (!is_string($css_file)) continue;
			wp_enqueue_style(
				$handle . '-script-style-' . $i,
				$dist_url . '/' . ltrim($css_file, '/'),
				[],
				null
			);
		}
	}

	// JS
	if (!isset($entry_data['file']) || !is_string($entry_data['file'])) return;
	wp_enqueue_script(
		$handle . '-script',
		$dist_url . '/' . ltrim($entry_data['file'], '/'),
		[],
		null,
		['in_footer' => true]
	);
	wp_script_add_data($handle . '-script', 'type', 'module');
}

/**
 * Enqueue Vite stylesheet entry (dev/prod)
 *
 * @param string $entry  Example: 'src/assets/sass/style.scss'
 * @param string $handle Example: 't2025'
 */
function t2025_enqueue_vite_style_entry(string $entry, string $handle = 't2025'): void {
	if (t2025_vite_is_dev()) {
		t2025_enqueue_vite_client($handle);
		$dev = t2025_vite_dev_server();
		wp_enqueue_style(
			$handle . '-vite-style',
			$dev . '/' . ltrim($entry, '/'),
			[],
			null
		);
		return;
	}

	$manifest_path = t2025_vite_manifest_path();
	if (!file_exists($manifest_path)) return;

	$manifest_raw = file_get_contents($manifest_path);
	if ($manifest_raw === false) return;

	$manifest = json_decode($manifest_raw, true);
	if (!is_array($manifest) || !isset($manifest[$entry]) || !is_array($manifest[$entry])) return;

	$dist_url = t2025_vite_dist_url();
	$entry_data = $manifest[$entry];

	if (!isset($entry_data['file']) || !is_string($entry_data['file'])) return;
	wp_enqueue_style(
		$handle . '-style',
		$dist_url . '/' . ltrim($entry_data['file'], '/'),
		[],
		null
	);
}


