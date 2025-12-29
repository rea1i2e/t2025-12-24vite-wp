<?php get_header(); ?>
<main class="p-main-sub">
  <section class="p-404" id="404">
    <div class="p-404__inner l-inner">
     <h1 class="p-404__heading">
      404 Not Found
     </h1>
     <p class="p-404__text">
      ページが見つかりませんでした。
     </p>
     <div class="p-404__button">
      <?php get_template_part('components/c-button', null, [
        'text' => 'トップへ戻る',
        'href' => ty_get_page(),
      ]); ?>
     </div>
    </div>
  </section>
  
</main>
<?php get_footer(); ?>


