<?php get_header(); ?>
<main class="p-main-sub">
  <div class="p-single">
    <div class="p-single__inner l-inner">
      <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
          <div class="p-single__head">
            <?php if (has_post_thumbnail()) : ?>
              <figure class="p-single__thumbnail">
                <?php ty_display_thumbnail('full', 'eager'); ?>
              </figure>
            <?php endif; ?>
            <div class="p-single__meta">
              <time class="p-single__date" datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>">
                公開日：<?php echo get_the_date('Y.m.d'); ?>
              </time>
              <?php
              $terms = get_the_terms(get_the_ID(), 'category');
              if ($terms && !is_wp_error($terms)) : ?>
                <div class="p-single__terms">ターム：
                  <?php foreach ($terms as $term) : ?>
                    <p class="p-single__term">
                      <a href="<?php echo esc_url(get_term_link($term)); ?>" class="p-single__term-link">
                        <?php echo esc_html($term->name); ?>
                      </a>
                    </p>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
            <h1 class="p-single__title">
              タイトル：<?php the_title() ?>
            </h1>
          </div>
          <div class="p-single__content p-content">
            <?php the_content() ?>
          </div>

          <?php get_template_part('components-demo/p-post-nav'); ?>
    </div>
  </div>
<?php endwhile; ?>
<?php endif; ?>
</main>
<?php get_footer(); ?>