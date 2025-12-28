<?php

declare(strict_types=1);

/**
 * Vite ヘルパー（WordPress の enqueue 用）
 *
 * - 開発時（dev）: Vite dev server から読み込む（HMR）
 * - 本番時（prod）: dist の manifest.json を参照して読み込む
 */


if (!defined('T2025_VITE_DEV_SERVER')) {
	define('T2025_VITE_DEV_SERVER', is_ssl() ? 'https://localhost:5173' : 'http://localhost:5173');
}

// 開発中かどうかを判定する
function t2025_vite_is_dev(): bool
{
	$dev_server = defined('T2025_VITE_DEV_SERVER') ? (string) T2025_VITE_DEV_SERVER : '';
	if ($dev_server === '') return false;

	$health_url = rtrim($dev_server, '/') . '/@vite/client';
	$response = wp_remote_head($health_url, ['timeout' => 0.25, 'sslverify' => false]);
	return !is_wp_error($response) && (int) wp_remote_retrieve_response_code($response) >= 200;
}

// Vite dev server のベースURLを返す（末尾スラッシュなし）
function t2025_vite_dev_server(): string
{
	$dev_server = defined('T2025_VITE_DEV_SERVER') ? (string) T2025_VITE_DEV_SERVER : 'http://localhost:5173';
	return rtrim($dev_server, '/');
}

// dist ディレクトリのURLを返す
function t2025_vite_dist_url(): string
{
	return rtrim(get_stylesheet_directory_uri(), '/') . '/dist';
}

// dist ディレクトリの実ファイルパスを返す
function t2025_vite_dist_path(): string
{
	return rtrim(get_stylesheet_directory(), '/') . '/dist';
}

// Vite の manifest.json の実ファイルパスを返す（出力先の差分を吸収）
function t2025_vite_manifest_path(): string
{
	// Vite はデフォルトで dist/.vite/manifest.json に manifest を出力する
	$path = t2025_vite_dist_path() . '/.vite/manifest.json';
	if (file_exists($path)) return $path;

	// 旧設定等で dist/manifest.json に出力される場合のフォールバック
	return t2025_vite_dist_path() . '/manifest.json';
}

// theme-assets.json の実ファイルパスを返す
function t2025_vite_theme_assets_path(): string
{
	return t2025_vite_dist_path() . '/theme-assets.json';
}

/**
 * theme-assets.json を読み込み、srcパス -> dist相対パス のマッピング配列を返す（1リクエスト内キャッシュ）
 *
 * @return array<string, string>
 */
function t2025_vite_theme_assets_map(): array
{
	static $cache = null;
	if (is_array($cache)) return $cache;

	$mapPath = t2025_vite_theme_assets_path();
	if (!file_exists($mapPath)) {
		$cache = [];
		return $cache;
	}

	$raw = file_get_contents($mapPath);
	if ($raw === false) {
		$cache = [];
		return $cache;
	}

	$map = json_decode($raw, true);
	if (!is_array($map)) {
		$cache = [];
		return $cache;
	}

	// string => string のみを残す
	$out = [];
	foreach ($map as $k => $v) {
		if (!is_string($k) || !is_string($v)) continue;
		$out[$k] = $v;
	}

	$cache = $out;
	return $cache;
}

/**
 * テーマアセットの URL を解決する
 *
 * PHP 側でビルド後のファイル名（ハッシュ付き）を知る必要がある場合のための関数です。
 *
 * @param string $srcPath 例: 'src/assets/images/demo/dummy1.jpg'
 */
function t2025_theme_asset_url(string $srcPath): string
{
	$srcPath = ltrim($srcPath, '/');

	if (t2025_vite_is_dev()) {
		return t2025_vite_dev_server() . '/' . $srcPath;
	}

	$map = t2025_vite_theme_assets_map();
	if (!isset($map[$srcPath]) || !is_string($map[$srcPath])) {
		return '';
	}

	return t2025_vite_dist_url() . '/' . ltrim($map[$srcPath], '/');
}

/**
 * `src/assets/images/**` 配下のテーマ画像 URL を解決する
 *
 * 例:
 * - t2025_theme_image_url('demo/dummy1.jpg')
 *
 * @param string $pathUnderImages 例: 'demo/dummy1.jpg'
 */
function t2025_theme_image_url(string $pathUnderImages): string
{
	$pathUnderImages = ltrim($pathUnderImages, '/');
	return t2025_theme_asset_url('src/assets/images/' . $pathUnderImages);
}

/**
 * Vite クライアントを enqueue（dev のみ）
 *
 * @param string $handle 例: 't2025'
 */
function t2025_enqueue_vite_client(string $handle = 't2025'): void
{
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
 * Vite のモジュールエントリ（JS）を enqueue（dev/prod）
 *
 * @param string $entry  例: 'src/assets/js/main.js'
 * @param string $handle 例: 't2025'
 */
function t2025_enqueue_vite_script_entry(string $entry, string $handle = 't2025'): void
{
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

	// JS エントリから import される CSS（例: ライブラリCSS）
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

	// JS 本体
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
 * Vite のスタイルエントリ（CSS）を enqueue（dev/prod）
 *
 * @param string $entry  例: 'src/assets/sass/style.scss'
 * @param string $handle 例: 't2025'
 */
function t2025_enqueue_vite_style_entry(string $entry, string $handle = 't2025'): void
{
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
