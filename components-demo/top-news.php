<section class="p-top-news l-top-news" id="top-news">
  <div class="p-top-news__inner l-inner">
  <div class="p-top-news__heading">
    <h2>お知らせ</h2>
  </div>  
    <div class="p-top-news__content">
      <?php
      $news_args = [
        'post_type' => 'post',
        'posts_per_page' => 3,
      ];
      $news_query = new WP_Query($news_args);
      if ($news_query->have_posts()) :
      ?>
        <div class="p-top-news__posts">
          <?php while ($news_query->have_posts()) : $news_query->the_post(); ?>
            <article class="p-top-news__post">
              <a class="p-top-news__post-link" href="<?php the_permalink(); ?>">
                <div class="p-top-news__post-meta">
                  <time class="p-top-news__date" datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>">
                    <?php echo esc_html(get_the_date('Y.m.d')); ?>
                  </time>
                  <?php
                  $news_categories = get_the_terms(get_the_ID(), 'category');
                  if ($news_categories && !is_wp_error($news_categories)) :
                  ?>
                    <div class="p-top-news__terms">
                      <?php foreach ($news_categories as $category) : ?>
                        <p class="p-top-news__term"><?php echo esc_html($category->name); ?></p>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
                <h3 class="p-top-news__title"><?php the_title(); ?></h3>
              </a>
            </article>
          <?php endwhile; ?>
        </div>
        <div class="p-top-news__more">
          <a href="<?php echo get_post_type_archive_link('post'); ?>" class="c-button">more</a>
        </div>
      <?php else : ?>
        <p class="p-top-news__no-post c-no-post">新着情報はありません。</p>
      <?php
      endif;
      wp_reset_postdata();
      ?>
    </div>
  </div>
</section>