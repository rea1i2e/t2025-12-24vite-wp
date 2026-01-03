<?php

declare(strict_types=1);

/**
 * WordPressバージョン情報の削除
 *
 * `<head>` タグ内に出力されるWordPressのバージョン情報を削除する
 * これにより、攻撃者がWordPressのバージョンを特定しにくくなり、
 * 脆弱性を狙った攻撃を防ぐ
 *
 * @see https://digitalnavi.net/wordpress/6921/
 */
remove_action('wp_head', 'wp_generator');
