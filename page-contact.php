<?php get_header(); ?>
<main>
  <?php get_template_part('components/breadcrumb') ?>
  <div class="l-contact p-contact">
    <div class="p-contact__inner l-inner">
      <div class="p-contact__content">
        <p class="p-contact__description">
          お問い合わせはこちらまで
        </p>
        <div class="p-contact__container">
          <div class="p-contact__container-inner">
            <?php the_content(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
<?php get_footer(); ?>


