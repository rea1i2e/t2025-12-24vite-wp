<?php
declare(strict_types=1);

$ranking_sections = [
  ['id' => 'ranking-weekly',  'label' => '週間', 'range' => 'weekly'],
  ['id' => 'ranking-monthly', 'label' => '月間', 'range' => 'monthly'],
];

$ranking_data = [];
if (function_exists('wpp_get_ids')) {
  $current_post_type = get_post_type() ?: 'post';
  foreach ($ranking_sections as $section) {
    $ranking_data[$section['range']] = wpp_get_ids([
      'range'     => $section['range'],
      'limit'     => 7,
      'post_type' => $current_post_type,
    ]);
  }
}
?>
<div class="p-ranking">
  <?php foreach ($ranking_sections as $section) : ?>
    <section class="p-ranking__section" id="<?php echo esc_attr($section['id']); ?>">
      <h2 class="p-ranking__heading"><?php echo esc_html($section['label']); ?></h2>
      <?php
      $items = $ranking_data[$section['range']] ?? [];
      if (!empty($items)) :
      ?>
        <ol class="p-ranking__list">
          <?php foreach ($items as $index => $post_id) : ?>
            <?php
            $title = get_the_title($post_id);
            $link  = get_permalink($post_id);
            $rank  = $index + 1;
            ?>
            <li class="p-ranking__item">
              <a href="<?php echo esc_url($link); ?>" class="p-ranking__link">
                <span class="p-ranking__rank"><?php echo $rank; ?></span>
                <span class="p-ranking__title"><?php echo esc_html($title); ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ol>
      <?php else : ?>
        <p class="p-ranking__empty"></p>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>
</div>
