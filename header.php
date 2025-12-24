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
          <?php foreach (get_nav_items() as $item) : ?>
            <?php if (in_array($item['slug'], ['site-policy'])) continue; // 一部除外 ?>
            <?php
            // URL生成
            $item_url = '';
            if (isset($item['external']) && $item['external']) {
              $item_url = esc_url($item['slug']);
            } elseif ($item['slug'] === 'top') {
              $item_url = get_page_path();
            } else {
              $item_url = get_page_path($item['slug']);
            }
            // 現在のページがナビゲーションのリンク先と一致しているかどうかを判定
            if (is_front_page() && $item['slug'] === 'top') {
              $is_current_class = 'is-current';
            } elseif (is_page($item['slug']) || is_post_type_archive($item['slug']) || is_category($item['slug']) || is_tax($item['slug'])) {
              $is_current_class = 'is-current';
            } else {
              $is_current_class = '';
            }
            // target属性
            $target_attr = '';
            if (isset($item['target'])) {
              $target_attr = ' target="' . esc_attr($item['target']) . '"';
              if ($item['target'] === '_blank') {
                $target_attr .= ' rel="noopener noreferrer"';
              }
            }
            ?>
            <li class="p-header__pc-nav-item <?php echo isset($item['modifier']) ? 'p-header__pc-nav-item--' . $item['modifier'] : ''; ?> <?php echo $is_current_class ?>">
              <?php if (isset($item['children']) && !empty($item['children'])) : ?>
                <a class="p-header__pc-nav-link js-dropdown-trigger" href="<?php echo $item_url; ?>" <?php echo $target_attr; ?>><?php echo esc_html($item['text']); ?></a>
                <div class="p-header__pc-nav-submenu">
                  <ul class="p-header__pc-nav-submenu-items">
                    <?php foreach ($item['children'] as $child) : ?>
                      <?php
                      // 子メニューのURL生成
                      $child_url = '';
                      if (isset($child['external']) && $child['external']) {
                        $child_url = esc_url($child['slug']);
                      } elseif ($child['slug'] === 'top') {
                        $child_url = get_page_path();
                      } else {
                        $child_url = get_page_path($child['slug']);
                      }
                      // 子メニューの現在ページ判定
                      $child_is_current = '';
                      if (is_front_page() && $child['slug'] === 'top') {
                        $child_is_current = 'is-current';
                      } elseif (is_page($child['slug']) || is_post_type_archive($child['slug']) || is_category($child['slug']) || is_tax($child['slug'])) {
                        $child_is_current = 'is-current';
                      }
                      // 子メニューのtarget属性
                      $child_target_attr = '';
                      if (isset($child['target'])) {
                        $child_target_attr = ' target="' . esc_attr($child['target']) . '"';
                        if ($child['target'] === '_blank') {
                          $child_target_attr .= ' rel="noopener noreferrer"';
                        }
                      }
                      ?>
                      <li class="p-header__pc-nav-submenu-item <?php echo $child_is_current; ?>">
                        <a class="p-header__pc-nav-submenu-link" href="<?php echo $child_url; ?>" <?php echo $child_target_attr; ?>><?php echo esc_html($child['text']); ?></a>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php else : ?>
                <a class="p-header__pc-nav-link" href="<?php echo $item_url; ?>" <?php echo $target_attr; ?>><?php echo esc_html($item['text']); ?></a>
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
        <?php foreach (get_nav_items() as $item) : ?>
          <?php if (in_array($item['slug'], ['site-policy'])) continue; // 一部除外 ?>
          <?php
          // URL生成
          $item_url = '';
          if (isset($item['external']) && $item['external']) {
            $item_url = esc_url($item['slug']);
          } elseif ($item['slug'] === 'top') {
            $item_url = get_page_path();
          } else {
            $item_url = get_page_path($item['slug']);
          }

          // 現在のページがナビゲーションのリンク先と一致しているかどうかを判定
          if (is_front_page() && $item['slug'] === 'top') {
            $is_current_class = 'is-current';
          } elseif (is_page($item['slug']) || is_post_type_archive($item['slug']) || is_category($item['slug']) || is_tax($item['slug'])) {
            $is_current_class = 'is-current';
          } else {
            $is_current_class = '';
          }

          // target属性
          $target_attr = '';
          if (isset($item['target'])) {
            $target_attr = ' target="' . esc_attr($item['target']) . '"';
            if ($item['target'] === '_blank') {
              $target_attr .= ' rel="noopener noreferrer"';
            }
          }
          ?>
          <li class="p-toggle-nav-items__item <?php echo isset($item['modifier']) ? 'p-toggle-nav-items__item--' . $item['modifier'] : ''; ?> <?php echo isset($item['children']) && !empty($item['children']) ? 'js-toggle-item' : ''; ?> <?php echo $is_current_class ?>">
            <?php if (isset($item['children']) && !empty($item['children'])) : ?>
              <button class="p-toggle-nav-items__button js-toggle-item-trigger" aria-expanded="false" type="button"><?php echo esc_html($item['text']); ?></button>
              <ul class="p-toggle-nav-items__submenu js-toggle-item-content" style="display: none;">
                <?php foreach ($item['children'] as $child) : ?>
                  <?php
                  // 子メニューのURL生成
                  $child_url = '';
                  if (isset($child['external']) && $child['external']) {
                    $child_url = esc_url($child['slug']);
                  } elseif ($child['slug'] === 'top') {
                    $child_url = get_page_path();
                  } else {
                    $child_url = get_page_path($child['slug']);
                  }

                  // 子メニューの現在ページ判定
                  $child_is_current = '';
                  if (is_front_page() && $child['slug'] === 'top') {
                    $child_is_current = 'is-current';
                  } elseif (is_page($child['slug']) || is_post_type_archive($child['slug']) || is_category($child['slug']) || is_tax($child['slug'])) {
                    $child_is_current = 'is-current';
                  }

                  // 子メニューのtarget属性
                  $child_target_attr = '';
                  if (isset($child['target'])) {
                    $child_target_attr = ' target="' . esc_attr($child['target']) . '"';
                    if ($child['target'] === '_blank') {
                      $child_target_attr .= ' rel="noopener noreferrer"';
                    }
                  }
                  ?>
                  <li class="p-toggle-nav-items__sub-item <?php echo $child_is_current; ?>">
                    <a class="p-toggle-nav-items__sub-link" href="<?php echo $child_url; ?>" <?php echo $child_target_attr; ?>><?php echo esc_html($child['text']); ?></a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else : ?>
              <a class="p-toggle-nav-items__link" href="<?php echo $item_url; ?>" <?php echo $target_attr; ?>><?php echo esc_html($item['text']); ?></a>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </nav>


