<?php get_header(); ?>
<main class="p-main-sub">
  <div class="p-news l-news">
    <div class="p-news__inner l-inner">
    <?php get_template_part('components/breadcrumb') ?>
      <h1 class="p-works__page-title c-page-title">
        <?php single_cat_title(); ?>
      </h1>
      <div class="p-news__items p-news-items">
        <?php if (have_posts()) : ?>
          <?php while (have_posts()) : the_post(); ?>
            <article class="p-news-items__post" data-fadein>
              <a href="<?php the_permalink(); ?>" class="p-news-items__link">
                <div class="p-news-items__wrap">
                  <p class="p-news-items__date">
                    <time class="" datetime="<?php echo get_the_date('Y-m-d'); ?>">
                      <?php echo get_the_date('Y / m / d'); ?>
                    </time>
                  </p>
                  <h2 class="p-news-items__title"><?php the_title(); ?></h2>
                </div>
              </a>
            </article>
          <?php endwhile; ?>
          <div class="p-news-items__pagenavi">
            <?php get_template_part('components/p-pagenavi'); ?>
          </div>
        <?php else : ?>
          <p class="p-news-items__no-post c-no-post">
            新着情報はありません。
          </p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
<?php get_footer(); ?>


