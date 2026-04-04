<?php
$sns_items = [
  [
    'url' => SNS_FACEBOOK_URL,
    'img_src' => 'common/icon_facebook.png',
    'img_src_white' => 'common/icon_facebook_white.png',
    'alt' => 'Facebook',
  ],
  [
    'url' => SNS_YOUTUBE_URL,
    'img_src' => 'common/icon_youtube.png',
    'img_src_white' => 'common/icon_youtube_white.png',
    'alt' => 'YouTube',
  ]
];

$color = isset($args['color']) ? $args['color'] : '';
?>
<ul class="p-sns-items">
  <?php foreach ($sns_items as $item): ?>
    <li class="p-sns-items__item">
      <a class="p-sns-items__link" href="<?php echo $item['url']; ?>" target="_blank" rel="noopener noreferrer">
        <?php ty_img($color === 'white' ? $item['img_src_white'] : $item['img_src'], $item['alt']); ?>
      </a>
    </li>
  <?php endforeach; ?>
</ul>