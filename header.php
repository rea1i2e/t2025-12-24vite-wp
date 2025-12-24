<?php

declare(strict_types=1);

$logo_tag = is_front_page() ? 'h1' : 'div';
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<meta name="format-detection" content="telephone=no">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="p-header l-header">
	<div class="p-header__inner">
		<<?php echo esc_attr($logo_tag); ?> class="p-header__logo">
			<a href="<?php echo esc_url(home_url('/')); ?>" class="p-header__logo-link">
				<img src="<?php echo esc_url(get_theme_file_uri('src/public/vite.svg')); ?>" alt="vite">
			</a>
		</<?php echo esc_attr($logo_tag); ?>>

		<button type="button" class="p-header__menu-button c-menu-button" id="js-menu" aria-controls="js-drawer" aria-expanded="false" aria-label="メニューを開閉する">
			<span class="c-menu-button__line"></span>
		</button>

		<nav class="p-header__nav" aria-label="グローバルナビゲーション">
			<?php
			wp_nav_menu([
				'theme_location' => 'global',
				'container'      => false,
				'menu_class'     => 'p-header__nav-items',
				'fallback_cb'    => false,
				'depth'          => 1,
			]);
			?>
		</nav>
	</div>
</header>

<div id="js-drawer" class="p-drawer l-drawer" aria-hidden="true" inert>
	<div class="p-drawer__inner">
		<nav class="p-drawer__nav" aria-label="ドロワーメニュー">
			<?php
			wp_nav_menu([
				'theme_location' => 'global',
				'container'      => false,
				'menu_class'     => 'p-drawer__nav-items',
				'menu_id'        => 'js-drawer-menu',
				'fallback_cb'    => false,
				'depth'          => 1,
			]);
			?>
		</nav>
	</div>
</div>


