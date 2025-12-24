<?php
get_header();
get_template_part('components/sub-mv', null, [
  'title_ja' => get_the_title(),
]);
?>

<main class="p-page l-page">
  <div class="p-page__inner l-inner">
    <div class="p-page__contents">
      <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
          <div class="p-page__content p-content" data-fadein>
            <?php the_content() ?>
          </div>
    </div>
  <?php endwhile; ?>
<?php endif; ?>
</main>
<?php get_footer(); ?>


