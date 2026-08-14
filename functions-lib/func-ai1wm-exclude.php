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
 * - 開発用ファイル（src/, node_modules/, .git, ai-docs, raw, tools など）は除外
 * - ビルド成果物（dist/）は含める（移行先で再ビルドしない想定）
 * - 開発環境用設定ファイル（vite.config.js, package.json, env.deploy.example など）は除外
 * - FTP_EXCLUDE（.github/workflows/deploy.yml）と揃える
 *   ※ ai1wm はパスの前方一致のみでグロブ（拡張子ワイルドカード等）は使えないため、
 *      FTP_EXCLUDE のうちグロブでしか書けない項目（.map 等）は対象外
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
		$exclude_filters[] = $theme_dir . '/Thumbs.db';
		$exclude_filters[] = $theme_dir . '/.vscode';

		// 開発用ディレクトリ
		$exclude_filters[] = $theme_dir . '/src';
		$exclude_filters[] = $theme_dir . '/node_modules';
		$exclude_filters[] = $theme_dir . '/scripts';
		$exclude_filters[] = $theme_dir . '/raw';
		$exclude_filters[] = $theme_dir . '/tools';
		$exclude_filters[] = $theme_dir . '/config';
		$exclude_filters[] = $theme_dir . '/staging';
		$exclude_filters[] = $theme_dir . '/.husky';

		// AI 関連（ドキュメント・設定・QA 生成物）
		$exclude_filters[] = $theme_dir . '/ai-docs';
		$exclude_filters[] = $theme_dir . '/qa-screenshots';
		$exclude_filters[] = $theme_dir . '/.cursor';
		$exclude_filters[] = $theme_dir . '/.claude';
		$exclude_filters[] = $theme_dir . '/.cursorrules';
		$exclude_filters[] = $theme_dir . '/AGENTS.md';
		$exclude_filters[] = $theme_dir . '/CLAUDE.md';
		$exclude_filters[] = $theme_dir . '/capture-qa.config.json';

		// 設定ファイル
		$exclude_filters[] = $theme_dir . '/package.json';
		$exclude_filters[] = $theme_dir . '/package-lock.json';
		$exclude_filters[] = $theme_dir . '/vite.config.js';
		$exclude_filters[] = $theme_dir . '/postcss.config.cjs';
		$exclude_filters[] = $theme_dir . '/env.deploy.example';
		$exclude_filters[] = $theme_dir . '/.env.deploy';
		$exclude_filters[] = $theme_dir . '/vite.hot';

		// ドキュメント
		$exclude_filters[] = $theme_dir . '/README.md';
		$exclude_filters[] = $theme_dir . '/docs';

		return $exclude_filters;
	}
);
