<?php declare(strict_types=1); ?>

<h3>1. loading: eager, fetchpriority: high（FVを想定）</h3>
<p>遅延読み込みなし・LCP候補</p>
<?php
ty_img('demo/dummy1.jpg', 'dummy1.jpg', true, 'fetchpriority="high"');
?>

<h3>2. loading, fetchpriority指定なし（loading="lazy" fetchpriority属性指定なし）</h3>
<p>遅延読み込みあり（これがデフォルト）</p>
<?php ty_img('demo/dummy2.jpg', 'dummy2.jpgのalt'); ?>

<h3>3. pictureタグ（ty_picture_img を使用）</h3>
<p>PC=demo/dummy3.jpg、SP=demo/dummy3_sp.jpg（命名規則で自動導出）</p>
<?php
ty_picture_img('demo/dummy3.jpg', 'dummy3.jpgのalt');
?>
<p>PCとSPでファイル形式が異なる場合は、第2引数で指定する</p>
<?php
ty_picture_img('demo/dummy3.jpg', 'demo/dummy3_sp.png', 'dummy3.jpgのalt');
?>

<h3>4. altを省略した場合</h3>
<p>altを省略するとalt=""となる</p>
<?php ty_img('demo/dummy3.jpg'); ?>