<?php

declare(strict_types=1);

/**
 * Theme setup (base)
 */
function ty_setup(): void {
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
}
add_action('after_setup_theme', 'ty_setup');


