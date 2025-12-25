<?php
/**
 * パンくずリスト ※Breadcrumb NavXTの場合
 */
if (function_exists('bcn_display') && !is_front_page()) : ?>
  <div class="p-breadcrumb l-breadcrumb">
    <div class="p-breadcrumb__inner l-inner">
      <div class="p-breadcrumb__wrapper" typeof="BreadcrumbList" vocab="https://schema.org/">
        <?php bcn_display(); ?>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php
/**
 * パンくずリスト ※All-in-One SEOの場合
 */
/*
if (function_exists('aioseo_breadcrumbs') && !is_front_page()) : ?>
  <div class="p-breadcrumb l-breadcrumb">
    <div class="p-breadcrumb__inner l-inner">
      <?php aioseo_breadcrumbs(); ?>
    </div>
  </div>
  <?php endif; ?>
*/


