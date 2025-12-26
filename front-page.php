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
  <section class="p-demo l-demo" id="demo">
    <div class="p-demo__inner l-inner">
      <div class="p-demo__heading">
        <?php get_template_part('components/c-heading', null, [
          'text_en' => 'img',
          'text_ja' => 'imgタグの出力',
        ]); ?>
      </div>
      <div class="p-demo__content">
        <h3>loading: eager, fetchpriority: high</h3>
        <p>遅延読み込みなし・LCP候補</p>
        <?php
        echo t2025_img_image(
          'demo/dummy1.jpg',
          'dummy1.jpg',
          [
            'loading' => 'eager',
            'fetchpriority' => 'high',
          ]
        );
        ?>
        <h3>loading, fetchpriority指定なし（loading="lazy" fetchpriority属性指定なし）</h3>
        <p>遅延読み込みあり・LCP候補</p>
        <?php
        echo t2025_img(
          'src/assets/images/demo/dummy2.jpg',
          'dummy2.jpg'
        );
        ?>
        <h3>pictureタグによる出し分け</h3>
        <p>768px以上ではdummy3.jpg（テングザル）、767px以下ではdummy2.jpg（オランウータン）</p>
        <picture>
          <source srcset="<?php echo t2025_theme_asset_url('src/assets/images/demo/dummy2.jpg'); ?>" media="(max-width: 767px)">
          <img src="<?php echo t2025_theme_asset_url('src/assets/images/demo/dummy3.jpg'); ?>" alt="dummy3.jpg">
        </picture>
        <h3>t2025_img_image()を使用した場合</h3>
        <p>記述は減るけど、Cursor上で左側に表示されない</p>
        <?php echo t2025_img_image('demo/dummy3.jpg', 'dummy3.jpg'); ?>
      </div>
    </div>
  </section>
  <section class="p-demo l-demo" id="demo-dialog">
    <div class="p-demo__inner l-inner">
      <div class="p-demo__heading">
        <h2>モーダル</h2>
      </div>
      <div class="p-demo__content">
        <?php get_template_part('components/p-dialog'); ?>
      </div>
    </div>
  </section>
  <section class="p-demo l-demo" id="demo-tab">
    <div class="p-demo__inner l-inner">
      <div class="p-demo__heading">
        <h2>タブ切り替え</h2>
      </div>
      <div class="p-demo__content">
        <?php get_template_part('components/p-tab'); ?>
      </div>
    </div>
  </section>
</main>
<?php get_footer(); ?>