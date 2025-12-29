<?php

declare(strict_types=1);

/**
 * All-in-One WP Migration のエクスポートデータから除外するディレクトリ/ファイルを設定
 *
 * =========================================
 * 使い方（例）
 * =========================================
 *
 * ▼ 除外リストに追加する
 *   $exclude_filters[] = $theme_dir . '/除外したいパス';
 *
 * 注意:
 * - 開発用ファイル（src/, node_modules/, .git など）は除外
 * - ビルド成果物（dist/）は含める（移行先で再ビルドしない想定）
 * - 開発環境用設定ファイル（vite.config.js, package.json, env.deploy.example など）は除外
 */

add_filter(
	'ai1wm_exclude_themes_from_export',
	function (array $exclude_filters): array {
		$theme_dir = basename(get_theme_root() . '/' . get_stylesheet());

		// バージョン管理
		$exclude_filters[] = $theme_dir . '/.git';
		$exclude_filters[] = $theme_dir . '/.gitignore';
		$exclude_filters[] = $theme_dir . '/.gitattributes';
		$exclude_filters[] = $theme_dir . '/.github';

		// システムファイル
		$exclude_filters[] = $theme_dir . '/.DS_Store';
		$exclude_filters[] = $theme_dir . '/.vscode';

		// 開発用ディレクトリ
		$exclude_filters[] = $theme_dir . '/src';
		$exclude_filters[] = $theme_dir . '/node_modules';
		$exclude_filters[] = $theme_dir . '/scripts';
		
		// 設定ファイル
		$exclude_filters[] = $theme_dir . '/package.json';
		$exclude_filters[] = $theme_dir . '/package-lock.json';
		$exclude_filters[] = $theme_dir . '/vite.config.js';
		$exclude_filters[] = $theme_dir . '/postcss.config.cjs';
		$exclude_filters[] = $theme_dir . '/env.deploy.example';
		
		// ドキュメント
		$exclude_filters[] = $theme_dir . '/README.md';
		$exclude_filters[] = $theme_dir . '/docs';

		return $exclude_filters;
	}
);
