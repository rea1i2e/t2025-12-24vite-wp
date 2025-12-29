<footer class="p-footer l-footer">
  <div class="p-footer__inner l-inner">
    <nav class="p-footer__nav" aria-label="フッターナビゲーション">
      <ul class="p-footer__nav-list">
        <?php foreach (get_nav_items() as $item) : ?>
          <?php if (($item['slug'] ?? '') === 'top') continue; // 一部除外 
          ?>

          <?php
          $slug = (string) ($item['slug'] ?? '');
          $text = (string) ($item['text'] ?? '');
          if ($slug === '' || $text === '') continue;

          $is_external = !empty($item['external']);

          // URL生成
          if ($is_external) {
            $item_url = esc_url($slug);
          } else {
            $item_url = ($slug === 'top') ? ty_get_page() : ty_get_page($slug);
          }

          // 現在ページ判定
          if (is_front_page() && $slug === 'top') {
            $is_current_class = 'is-current';
          } elseif (is_page($slug) || is_post_type_archive($slug) || is_category($slug) || is_tax($slug)) {
            $is_current_class = 'is-current';
          } else {
            $is_current_class = '';
          }

          // target属性
          $target_attr = '';
          if (!empty($item['target'])) {
            $target = (string) $item['target'];
            $target_attr = ' target="' . esc_attr($target) . '"';
            if ($target === '_blank') {
              $target_attr .= ' rel="noopener noreferrer"';
            }
          }

          $has_children = !empty($item['children']) && is_array($item['children']);
          $modifier = !empty($item['modifier']) ? (string) $item['modifier'] : '';

          $li_classes = array_filter([
            'p-footer__nav-item',
            $modifier !== '' ? 'p-footer__nav-item--' . $modifier : '',
            $is_current_class,
          ]);
          ?>

          <li class="<?php echo esc_attr(implode(' ', $li_classes)); ?>">
            <?php if ($has_children) : ?>
              <a class="p-footer__nav-link" href="<?php echo $item_url; ?>" <?php echo $target_attr; ?>>
                <?php echo esc_html($text); ?>
              </a>

              <ul class="p-footer__nav-sub-list">
                <?php foreach ($item['children'] as $child) : ?>
                  <?php
                  if (!is_array($child)) continue;
                  $child_slug = (string) ($child['slug'] ?? '');
                  $child_text = (string) ($child['text'] ?? '');
                  if ($child_slug === '' || $child_text === '') continue;

                  $child_is_external = !empty($child['external']);
                  if ($child_is_external) {
                    $child_url = esc_url($child_slug);
                  } else {
                    $child_url = ($child_slug === 'top') ? ty_get_page() : ty_get_page($child_slug);
                  }

                  $child_is_current = '';
                  if (is_front_page() && $child_slug === 'top') {
                    $child_is_current = 'is-current';
                  } elseif (is_page($child_slug) || is_post_type_archive($child_slug) || is_category($child_slug) || is_tax($child_slug)) {
                    $child_is_current = 'is-current';
                  }

                  $child_target_attr = '';
                  if (!empty($child['target'])) {
                    $child_target = (string) $child['target'];
                    $child_target_attr = ' target="' . esc_attr($child_target) . '"';
                    if ($child_target === '_blank') {
                      $child_target_attr .= ' rel="noopener noreferrer"';
                    }
                  }
                  ?>

                  <li class="p-footer__nav-sub-item <?php echo esc_attr($child_is_current); ?>">
                    <a class="p-footer__nav-sub-link" href="<?php echo $child_url; ?>" <?php echo $child_target_attr; ?>>
                      <?php echo esc_html($child_text); ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else : ?>
              <a class="p-footer__nav-link" href="<?php echo $item_url; ?>" <?php echo $target_attr; ?>>
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