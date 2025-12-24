<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>
  <header class="p-header" id="js-header">
    <div class="p-header__inner">
      <?php $tag = (is_front_page()) ? 'h1' : 'div'; ?>
      <<?php echo $tag; ?> class="p-header__logo">
        <a class="p-header__logo-link" href="<?php page_path(); ?>">
          <img src="<?php img_path('/common/logo.svg'); ?>" alt="">
        </a>
      </<?php echo $tag; ?>>
      <button class="p-header__menu-button c-menu-button" id="js-menu" type="button" aria-controls="js-drawer" aria-expanded="false" aria-label="メニューを開閉する">
        <span></span>
      </button>
      <nav class="p-header__pc-nav">
        <ul class="p-header__pc-nav-items">
          <?php
          echo t2025_menu_items_html(
            't2025_primary',
            'p-header__pc-nav-item',
            'p-header__pc-nav-link',
            true
          );
          ?>
        </ul>
      </nav>
    </div>
  </header>
  <nav class="p-drawer" id="js-drawer" aria-hidden="true">
    <div class="p-drawer__inner l-inner">
      <ul class="p-drawer__nav-items" id="js-drawer-menu">
        <?php
        echo t2025_menu_items_html(
          't2025_primary',
          'p-drawer__nav-item',
          'p-drawer__nav-link',
          true
        );
        ?>
      </ul>
    </div>
  </nav>


