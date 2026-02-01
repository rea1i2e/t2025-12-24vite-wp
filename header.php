<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="shortcut icon" href="<?php ty_theme('/favicon.ico'); ?>">
  <link rel="apple-touch-icon" href="<?php ty_theme('/apple-touch-icon.png'); ?>">
  <link rel="preload" href="<?php echo ty_vite_asset_url('src/assets/fonts/NotoSansJP-VF.woff2'); ?>" as="font" type="font/woff2" crossorigin>
  <?php if (is_front_page()) : $preload_image_url = ty_theme_image_url('demo/nagasaki1.jpg'); ?>
    <link rel="preload" href="<?php echo $preload_image_url; ?>" as="image">
  <?php endif; ?>
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>
  <header class="p-header" id="js-header">
    <div class="p-header__inner">
      <?php $tag = (is_front_page()) ? 'h1' : 'div'; ?>
      <<?php echo $tag; ?> class="p-header__logo">
        <a class="p-header__logo-link" href="<?php ty_page(); ?>">
          <?php echo ty_img('common/logo.svg', '', ['loading' => 'eager']); ?>
        </a>
      </<?php echo $tag; ?>>
      <button class="p-header__menu-button c-menu-button" id="js-menu" type="button" aria-controls="js-drawer" aria-expanded="false" aria-label="メニューを開閉する">
        <span></span>
      </button>
      <nav class="p-header__pc-nav">
        <ul class="p-header__pc-nav-items">
          <?php foreach (ty_get_nav_items() as $item) : ?>
            <?php /* 一部除外 */ ?>
            <?php if (in_array($item['slug'], ['privacy-policy', 'terms-of-use'])) continue; ?>
            <?php $link = ty_get_nav_item_link($item); ?>
            <li class="p-header__pc-nav-item <?php echo isset($item['modifier']) ? 'p-header__pc-nav-item--' . $item['modifier'] : ''; ?> <?php echo $link['current_class']; ?>">
              <?php if (isset($item['children']) && !empty($item['children'])) : ?>
                <a class="p-header__pc-nav-link js-dropdown-trigger" href="<?php echo $link['url']; ?>" <?php echo $link['target_attr']; ?>><?php echo esc_html($item['text']); ?></a>
                <div class="p-header__pc-nav-submenu">
                  <ul class="p-header__pc-nav-submenu-items">
                    <?php foreach ($item['children'] as $child) : ?>
                      <?php $link = ty_get_nav_item_link($child); ?>
                      <li class="p-header__pc-nav-submenu-item <?php echo $link['current_class']; ?>">
                        <a class="p-header__pc-nav-submenu-link" href="<?php echo $link['url']; ?>" <?php echo $link['target_attr']; ?>><?php echo esc_html($child['text']); ?></a>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php else : ?>
                <a class="p-header__pc-nav-link" href="<?php echo $link['url']; ?>" <?php echo $link['target_attr']; ?>><?php echo esc_html($item['text']); ?></a>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>
    </div>
  </header>
  <nav class="p-drawer" id="js-drawer" aria-hidden="true">
    <div class="p-drawer__inner l-inner">
      <ul class="p-drawer__nav-items p-toggle-nav-items" id="js-drawer-menu">
        <?php foreach (ty_get_nav_items() as $item) : ?>
          <?php if (in_array($item['slug'], ['site-policy'])) continue; // 一部除外 
          ?>
          <?php $link = ty_get_nav_item_link($item); ?>
          <li class="p-toggle-nav-items__item <?php echo isset($item['modifier']) ? 'p-toggle-nav-items__item--' . $item['modifier'] : ''; ?> <?php echo isset($item['children']) && !empty($item['children']) ? 'js-toggle-item' : ''; ?> <?php echo $link['current_class']; ?>">
            <?php if (isset($item['children']) && !empty($item['children'])) : ?>
              <button class="p-toggle-nav-items__button js-toggle-item-trigger" aria-expanded="false" type="button"><?php echo esc_html($item['text']); ?></button>
              <ul class="p-toggle-nav-items__submenu js-toggle-item-content" style="display: none;">
                <?php foreach ($item['children'] as $child) : ?>
                  <?php $link = ty_get_nav_item_link($child); ?>
                  <li class="p-toggle-nav-items__sub-item <?php echo $link['current_class']; ?>">
                    <a class="p-toggle-nav-items__sub-link" href="<?php echo $link['url']; ?>" <?php echo $link['target_attr']; ?>><?php echo esc_html($child['text']); ?></a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else : ?>
              <a class="p-toggle-nav-items__link" href="<?php echo $link['url']; ?>" <?php echo $link['target_attr']; ?>><?php echo esc_html($item['text']); ?></a>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="p-drawer__sns-items">
        <?php get_template_part('components-demo/p-sns-items'); ?>
      </div>
    </div>
  </nav>