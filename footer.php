<footer class="p-footer l-footer">
  <div class="p-footer__inner l-inner">
    <nav class="p-footer__nav" aria-label="フッターナビゲーション">
      <ul class="p-footer__nav-list">
        <?php foreach (ty_get_nav_items() as $item) : ?>
          <?php if (($item['slug'] ?? '') === 'top') continue; // 一部除外 ?>

          <?php
          // ナビ定義（func-nav-items.php）で slug/text 必須としているため、ここでは存在チェックのみ簡潔に扱う
          if (($item['text'] ?? '') === '') continue;

          $item_data = ty_get_nav_item_data($item, 'p-footer__nav-item');
          $has_children = !empty($item['children']) && is_array($item['children']);

          $li_classes = array_filter([
            'p-footer__nav-item',
            trim($item_data['modifier_class']),
            $item_data['current_class'],
          ]);

          $text = (string) $item['text'];
          ?>

          <li class="<?php echo esc_attr(implode(' ', $li_classes)); ?>"<?php echo $item_data['data_section_id_attr']; ?>>
            <?php if ($has_children) : ?>
              <a class="p-footer__nav-link" href="<?php echo $item_data['url']; ?>" <?php echo $item_data['target_attr']; ?>>
                <?php echo esc_html($text); ?>
              </a>

              <ul class="p-footer__nav-sub-list">
                <?php foreach ($item['children'] as $child) : ?>
                  <?php
                  $child_data = ty_get_nav_item_data($child, 'p-footer__nav-sub-item');
                  if (($child['text'] ?? '') === '') continue;
                  $child_text = (string) $child['text'];
                  ?>

                  <li class="p-footer__nav-sub-item<?php echo $child_data['modifier_class']; ?> <?php echo esc_attr($child_data['current_class']); ?>"<?php echo $child_data['data_section_id_attr']; ?>>
                    <a class="p-footer__nav-sub-link" href="<?php echo $child_data['url']; ?>" <?php echo $child_data['target_attr']; ?>>
                      <?php echo esc_html($child_text); ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else : ?>
              <a class="p-footer__nav-link" href="<?php echo $item_data['url']; ?>" <?php echo $item_data['target_attr']; ?>>
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