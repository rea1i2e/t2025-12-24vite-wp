<footer class="p-footer l-footer">
  <div class="p-footer__inner l-inner">
    <nav class="p-footer__nav" aria-label="フッターナビゲーション">
      <ul class="p-footer__nav-list">
        <?php foreach (ty_get_nav_items() as $item) : ?>
          <?php if (($item['slug'] ?? '') === 'top') continue; // 一部除外 
          ?>

          <?php
          $slug = (string) ($item['slug'] ?? '');
          $text = (string) ($item['text'] ?? '');
          if ($slug === '' || $text === '') continue;

          $link = ty_get_nav_item_link($item);
          $has_children = !empty($item['children']) && is_array($item['children']);
          $modifier = !empty($item['modifier']) ? (string) $item['modifier'] : '';

          $li_classes = array_filter([
            'p-footer__nav-item',
            $modifier !== '' ? 'p-footer__nav-item--' . $modifier : '',
            $link['current_class'],
          ]);
          ?>

          <li class="<?php echo esc_attr(implode(' ', $li_classes)); ?>">
            <?php if ($has_children) : ?>
              <a class="p-footer__nav-link" href="<?php echo $link['url']; ?>" <?php echo $link['target_attr']; ?>>
                <?php echo esc_html($text); ?>
              </a>

              <ul class="p-footer__nav-sub-list">
                <?php foreach ($item['children'] as $child) : ?>
                  <?php
                  if (!is_array($child)) continue;
                  $child_slug = (string) ($child['slug'] ?? '');
                  $child_text = (string) ($child['text'] ?? '');
                  if ($child_slug === '' || $child_text === '') continue;

                  $link = ty_get_nav_item_link($child);
                  ?>

                  <li class="p-footer__nav-sub-item <?php echo esc_attr($link['current_class']); ?>">
                    <a class="p-footer__nav-sub-link" href="<?php echo $link['url']; ?>" <?php echo $link['target_attr']; ?>>
                      <?php echo esc_html($child_text); ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else : ?>
              <a class="p-footer__nav-link" href="<?php echo $link['url']; ?>" <?php echo $link['target_attr']; ?>>
                <?php echo esc_html($text); ?>
              </a>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="p-footer__sns-items">
        <?php get_template_part('components-demo/p-sns-items', null, ['color' => 'white']); ?>
      </div>
    </nav>

    <div class="p-footer__bottom">
      <p class="p-footer__copyright">
        <small>copyright</small>
      </p>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>

</html>