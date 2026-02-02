<?php
get_header();
?>

<main class="p-main-sub">
  <?php get_template_part('components-demo/sub-mv', null, [
    'title_ja' => get_the_title(),
  ]); ?>
  <section class="p-about">
    <div class="p-about__inner l-inner">
      <h2>aboutページのコンテンツ</h2>
    </div>
  </section>
  <section class="p-demo l-demo js-section" id="access" style="background-color: beige;">
    <div class="p-demo__inner l-inner">
      <div class="p-demo__heading">
        <?php get_template_part('components/c-heading', null, [
          'text_en' => 'Access',
          'text_ja' => 'アクセス',
        ]); ?>
      </div>
      <div class="p-demo__content">
        <div class="p-demo__map">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3236.1111281379044!2d139.59100717579287!3d35.79720507254998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6018e97428adcaf5%3A0x93c79745bddfc5e3!2z5pyd6Zye5biC5b255omA!5e0!3m2!1sja!2sjp!4v1769981230670!5m2!1sja!2sjp" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
      </div>
    </div>
  </section>

</main>
<?php get_footer(); ?>