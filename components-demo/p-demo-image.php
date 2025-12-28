<?php declare(strict_types=1); ?>

<h3>1. loading: eager, fetchpriority: high（FVを想定）</h3>
<p>遅延読み込みなし・LCP候補</p>
<?php
echo ty_img(
	'demo/dummy1.jpg',
	'dummy1.jpg',
	[
		'loading' => 'eager',
		'fetchpriority' => 'high',
	]
);
?>

<h3>2. loading, fetchpriority指定なし（loading="lazy" fetchpriority属性指定なし）</h3>
<p>遅延読み込みあり（これがデフォルト）</p>
<?php echo ty_img('demo/dummy2.jpg', 'dummy2.jpgのalt'); ?>

<h3>3. pictureタグ（ty_picture_img を使用）</h3>
<p>768px以上ではdummy3.jpg（テングザル）、767px以下ではdummy2.jpg（オランウータン）</p>
<?php
echo ty_picture_img(
	'demo/dummy3.jpg', // PC用
	'demo/dummy2.jpg', // SP用
	'dummy3.jpgのalt',
	[
		'loading' => 'eager',
	]
);
?>

<h3>4. altを省略した場合</h3>
<p>altを省略するとalt=""となる</p>
<?php echo ty_img('demo/dummy3.jpg'); ?>