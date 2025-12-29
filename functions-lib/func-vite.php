<?php

declare(strict_types=1);

/**
 * Vite ヘルパー
 *
 * =========================================
 * 使い方（例）
 * =========================================
 *
 * ▼ フォントのURL解決（preload用など）
 *   ty_vite_asset_url('src/assets/fonts/NotoSansJP-VF.woff2')
 *
 * ▼ 画像のURL解決
 *   ty_theme_image_url('demo/dummy1.jpg')
 *   ※開発中、本番の判定とパスの解決はこのファイルで行う
 *   ※img, pictureタグの出力は、func-images.phpで扱う
 * 
 * ▼ JS/CSS の enqueue（通常は func-vite-assets.php で自動実行）
 *   ty_enqueue_vite_script_entry('src/assets/js/main.js', 'ty');
 *   ty_enqueue_vite_style_entry('src/assets/sass/style.scss', 'ty');
 */

if (!defined('TY_VITE_DEV_SERVER')) {
	define('TY_VITE_DEV_SERVER', is_ssl() ? 'https://localhost:5173' : 'http://localhost:5173');
}

// 開発中かどうかを判定
function ty_vite_is_dev(): bool
{
	$dev_server = defined('TY_VITE_DEV_SERVER') ? (string) TY_VITE_DEV_SERVER : '';
	if ($dev_server === '') return false;

	$health_url = rtrim($dev_server, '/') . '/@vite/client';
	$response = wp_remote_head($health_url, ['timeout' => 0.25, 'sslverify' => false]);
	return !is_wp_error($response) && (int) wp_remote_retrieve_response_code($response) >= 200;
}

// Vite dev server のベースURLを返す（末尾スラッシュなし）
function ty_vite_dev_server(): string
{
	$dev_server = defined('TY_VITE_DEV_SERVER') ? (string) TY_VITE_DEV_SERVER : 'http://localhost:5173';
	return rtrim($dev_server, '/');
}

// dist ディレクトリのURLを返す
function ty_vite_dist_url(): string
{
	return rtrim(get_stylesheet_directory_uri(), '/') . '/dist';
}

// dist ディレクトリの実ファイルパスを返す
function ty_vite_dist_path(): string
{
	return rtrim(get_stylesheet_directory(), '/') . '/dist';
}

// Vite の manifest.json の実ファイルパスを返す（出力先の差分を吸収）
function ty_vite_manifest_path(): string
{
	$path = ty_vite_dist_path() . '/.vite/manifest.json';
	if (file_exists($path)) return $path;
	return ty_vite_dist_path() . '/manifest.json';
}

// Vite の manifest.json を読み込み、エントリキー -> メタ情報配列を返す（1リクエスト内キャッシュ）
function ty_vite_manifest_map(): array
{
	static $cache = null;
	if (is_array($cache)) return $cache;

	$manifest_path = ty_vite_manifest_path();
	if (!file_exists($manifest_path)) {
		$cache = [];
		return $cache;
	}

	$raw = file_get_contents($manifest_path);
	if ($raw === false) {
		$cache = [];
		return $cache;
	}

	$manifest = json_decode($raw, true);
	if (!is_array($manifest)) {
		$cache = [];
		return $cache;
	}

	$cache = $manifest;
	return $cache;
}

// manifest.json を使って任意アセットのURLを解決する（dev/prod を吸収）
function ty_vite_asset_url(string $entry): string
{
	$entry = ltrim($entry, '/');

	if (ty_vite_is_dev()) {
		return ty_vite_dev_server() . '/' . $entry;
	}

	$manifest = ty_vite_manifest_map();
	if (!isset($manifest[$entry]) || !is_array($manifest[$entry])) return '';

	$data = $manifest[$entry];
	if (!isset($data['file']) || !is_string($data['file'])) return '';

	return ty_vite_dist_url() . '/' . ltrim($data['file'], '/');
}

// theme-assets.json の実ファイルパスを返す
function ty_vite_theme_assets_path(): string
{
	return ty_vite_dist_path() . '/theme-assets.json';
}

// theme-assets.json を読み込み、srcパス -> dist相対パス のマッピング配列を返す（1リクエスト内キャッシュ）
function ty_vite_theme_assets_map(): array
{
	static $cache = null;
	if (is_array($cache)) return $cache;

	$mapPath = ty_vite_theme_assets_path();
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

	$out = [];
	foreach ($map as $k => $v) {
		if (!is_string($k) || !is_string($v)) continue;
		$out[$k] = $v;
	}

	$cache = $out;
	return $cache;
}

// テーマアセットの URL を解決する（PHP 側でビルド後のファイル名を知る必要がある場合用）
function ty_theme_asset_url(string $srcPath): string
{
	$srcPath = ltrim($srcPath, '/');

	if (ty_vite_is_dev()) {
		return ty_vite_dev_server() . '/' . $srcPath;
	}

	$map = ty_vite_theme_assets_map();
	if (!isset($map[$srcPath]) || !is_string($map[$srcPath])) {
		return '';
	}

	return ty_vite_dist_url() . '/' . ltrim($map[$srcPath], '/');
}

// `src/assets/images/**` 配下のテーマ画像 URL を解決する
function ty_theme_image_url(string $pathUnderImages): string
{
	$pathUnderImages = ltrim($pathUnderImages, '/');
	return ty_theme_asset_url('src/assets/images/' . $pathUnderImages);
}

// Vite クライアントを enqueue（dev のみ）
function ty_enqueue_vite_client(string $handle = 'ty'): void
{
	if (!ty_vite_is_dev()) return;

	$dev = ty_vite_dev_server();
	wp_enqueue_script(
		$handle . '-vite-client',
		$dev . '/@vite/client',
		[],
		null,
		['in_footer' => false]
	);
	wp_script_add_data($handle . '-vite-client', 'type', 'module');
}

// Vite のモジュールエントリ（JS）を enqueue（dev/prod）
function ty_enqueue_vite_script_entry(string $entry, string $handle = 'ty'): void
{
	if (ty_vite_is_dev()) {
		ty_enqueue_vite_client($handle);
		$dev = ty_vite_dev_server();

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

	$manifest_path = ty_vite_manifest_path();
	if (!file_exists($manifest_path)) {
		return;
	}

	$manifest_raw = file_get_contents($manifest_path);
	if ($manifest_raw === false) return;

	$manifest = json_decode($manifest_raw, true);
	if (!is_array($manifest) || !isset($manifest[$entry]) || !is_array($manifest[$entry])) {
		return;
	}

	$dist_url = ty_vite_dist_url();
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
		['in_footer' => false]
	);
	wp_script_add_data($handle . '-script', 'type', 'module');
}

// Vite のスタイルエントリ（CSS）を enqueue（dev/prod）
function ty_enqueue_vite_style_entry(string $entry, string $handle = 'ty'): void
{
	if (ty_vite_is_dev()) {
		ty_enqueue_vite_client($handle);
		$dev = ty_vite_dev_server();
		wp_enqueue_style(
			$handle . '-vite-style',
			$dev . '/' . ltrim($entry, '/'),
			[],
			null
		);
		return;
	}

	$manifest_path = ty_vite_manifest_path();
	if (!file_exists($manifest_path)) return;

	$manifest_raw = file_get_contents($manifest_path);
	if ($manifest_raw === false) return;

	$manifest = json_decode($manifest_raw, true);
	if (!is_array($manifest) || !isset($manifest[$entry]) || !is_array($manifest[$entry])) return;

	$dist_url = ty_vite_dist_url();
	$entry_data = $manifest[$entry];

	if (!isset($entry_data['file']) || !is_string($entry_data['file'])) return;
	wp_enqueue_style(
		$handle . '-style',
		$dist_url . '/' . ltrim($entry_data['file'], '/'),
		[],
		null
	);
}
