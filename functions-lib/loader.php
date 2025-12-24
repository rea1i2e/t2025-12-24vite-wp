<?php

declare(strict_types=1);

/**
 * functions-lib loader
 *
 * Old-theme compatible style:
 * - Split theme functionality into multiple files under `functions-lib/`
 * - Load them from `functions.php`
 *
 * Notes:
 * - Some files require a deterministic order (e.g. Vite helpers before enqueue).
 * - Any new file added under functions-lib/ will be auto-loaded unless it starts with "_".
 */

$baseDir = get_theme_file_path('functions-lib');

// Load in a safe order (explicit).
$ordered = [
	'func-base.php',
	'func-vite.php',
	'func-images.php',
	'func-legacy.php',
	'func-menu.php',
	'func-nav-items.php',
	'func-vite-assets.php',
	'func-script-tag.php',
];

foreach ($ordered as $rel) {
	$path = $baseDir . '/' . $rel;
	if (file_exists($path)) {
		require_once $path;
	}
}

// Auto-load remaining php files (excluding already loaded).
foreach (glob($baseDir . '/*.php') as $file) {
	$name = basename($file);
	if ($name === 'loader.php') continue;
	if (str_starts_with($name, '_')) continue;
	if (in_array($name, $ordered, true)) continue;
	require_once $file;
}


