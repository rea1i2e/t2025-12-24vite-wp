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
