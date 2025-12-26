<?php get_header(); ?>
<main class="p-main-sub">
  <div class="p-archive">
    <div class="p-archive__inner l-inner">
      <?php get_template_part('components/breadcrumb') ?>
      <h1 class="p-archive__page-title">デフォルト投稿一覧 home.php</h1>
      <div class="p-archive__items p-archive-items">
        <?php if (have_posts()) : ?>
          <?php while (have_posts()) : the_post(); ?>
            <article class="p-archive-items__post" data-fadein>
              <a href="<?php the_permalink(); ?>" class="p-archive-items__link">
                <figure class="p-archive-items__thumbnail">
                  <?php display_thumbnail('full'); ?>
                </figure>
                <p class="p-archive-items__date">
                  <time class="" datetime="<?php echo get_the_date('Y-m-d'); ?>">
                    <?php echo get_the_date('Y / m / d'); ?>
                  </time>
                </p>
                <h2 class="p-archive-items__title"><?php the_title(); ?></h2>
              </a>
            </article>
          <?php endwhile; ?>
          <div class="p-archive-items__pagenavi">
            <?php get_template_part('components/p-pagenavi'); ?>
          </div>
        <?php else : ?>
          <p class="p-archive-items__no-post c-no-post">
            新着情報はありません。
          </p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
<?php get_footer(); ?>