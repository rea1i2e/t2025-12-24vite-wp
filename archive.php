<?php get_header(); ?>
<main class="p-main-sub">
  <?php get_template_part('components-demo/sub-mv', null, [
    'title_ja' => '制作実績一覧',
    'title_en' => 'archive.php',
    'image' => [
      'file' => 'demo/dummy6.jpg',
      // 'fileSp' => 'demo/dummy2.jpg', 省略可能
      'alt' => 'ボルネオの森',
    ],
  ]); ?>
  <div class="p-archive">
    <div class="p-archive__inner l-inner">
      <div class="p-archive__items p-archive-items">
        <div class="p-demo-cards">
          <?php 
          global $wp_query;
          $max_pages = $wp_query->max_num_pages;
          ?>
          <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
              <?php get_template_part('components-demo/p-archive-card-item'); ?>
            <?php endwhile; ?>
          <?php else : ?>
            <p class="p-archive-items__no-post c-no-post">
              新着情報はありません。
            </p>
          <?php endif; ?>
        </div>
        
        <?php if (isset($max_pages) && $max_pages > 1) : ?>
          <div class="p-demo-cards__load-more">
            <button id="js-load-more" class="c-button" type="button">
              もっと見る
            </button>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
<?php get_footer(); ?>