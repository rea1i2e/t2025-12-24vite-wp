<?php get_header(); ?>
<main>
  <div class="p-archive l-archive">
    <div class="p-archive__inner l-inner">
      <h1 class="p-works__page-title c-page-title">
        <?php single_cat_title(); ?>
      </h1>
      <?php if (have_posts()) : ?>
        <div class="p-archive__posts">
          <?php while (have_posts()) : the_post(); ?>
            <a href="<?php the_permalink(); ?>" class="p-archive__link" data-fadein>
              <figure class="p-archive__thumbnail">
                <?php display_thumbnail('full'); ?>
              </figure>
              <h2 class="p-archive__title">
                <?php the_title() ?>
              </h2>
              <p class="p-archive__date">
                <time datetime="<?php echo get_the_date('Y-m-d'); ?>">
                  <?php echo get_the_date('Y / m / d'); ?>
                </time>
              </p>
            </a>
          <?php endwhile; ?>
        </div>
        <div class="p-archive__pagenavi">
          <?php get_template_part('components/p-pagenavi'); ?>
        </div>
      <?php else : ?>
        <p class="p-archive__no-post c-no-post">
          新着情報はありません。
        </p>
      <?php endif; ?>
    </div>
  </div>
</main>
<?php get_footer(); ?>


