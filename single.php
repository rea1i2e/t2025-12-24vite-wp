<?php get_header(); ?>
<main class="p-main-sub">
  <div class="l-single p-single">
    <div class="p-single__inner l-inner">
      <div class="p-single__heading">

      </div>
      <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
          <div class="p-single__container">
            <div class="p-single__container-inner">
              <div class="p-single__head">
                <figure class="p-single__thumbnail">
                  <?php display_thumbnail('full', 'eager', false); ?>
                </figure>
                <div class="p-single__meta">
                  <time class="p-single__date" datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>">
                    <?php echo get_the_date('Y.m.d'); ?>
                  </time>
                </div>
                <h1 class="p-single__title">
                  <?php the_title() ?>
                </h1>
              </div>
              <div class="p-single__content p-content">
                <?php the_content() ?>
              </div>

              <!-- 仮 -->
              <div><?php previous_post_link('%link', '&laquo; %title'); ?></div>
              <div><?php next_post_link('%link', '%title &raquo;'); ?></div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php endif; ?>
</main>
<?php get_footer(); ?>


