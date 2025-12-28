<?php get_header(); ?>
<main class="p-main-top">
  <section class="p-demo l-demo" id="demo">
    <div class="p-demo__inner l-inner">
      <div class="p-demo__heading">
        <h1>WordPressサイト制作テンプレート（Vite）</h1>
      </div>
      <div class="p-demo__content">
        既存パーツの使い方
      </div>
    </div>
  </section>
  <section class="p-demo l-demo" id="demo-dialog">
    <div class="p-demo__inner l-inner">
      <div class="p-demo__heading">
        <h2>モーダル（dialog）</h2>
      </div>
      <div class="p-demo__content">
        <?php get_template_part('components-demo/p-dialog'); ?>
      </div>
    </div>
  </section>
  <section class="p-demo l-demo" id="demo-tab">
    <div class="p-demo__inner l-inner">
      <div class="p-demo__heading">
        <h2>タブ切り替え</h2>
      </div>
      <div class="p-demo__content">
        <?php get_template_part('components-demo/p-tab'); ?>
      </div>
    </div>
  </section>
  <section class="p-demo l-demo" id="demo-image">
    <div class="p-demo__inner l-inner">
      <div class="p-demo__heading">
        <?php get_template_part('components/c-heading', null, [
          'text_en' => 'img',
          'text_ja' => 'imgタグの出力',
        ]); ?>
      </div>
      <div class="p-demo__content">
        <?php get_template_part('components-demo/p-demo-image'); ?>
      </div>
    </div>
  </section>
</main>
<?php get_footer(); ?>