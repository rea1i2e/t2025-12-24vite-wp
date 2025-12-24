<footer class="p-footer l-footer">
  <div class="p-footer__inner l-inner">
  <div class="p-footer__nav">
      <ul class="p-footer__nav-items">
        <?php
        echo t2025_menu_items_html(
          't2025_footer',
          'p-footer__nav-item',
          'p-footer__nav-link',
          true
        );
        ?>
      </ul>
    <p class="p-footer__copyright">
      <small>copyright</small>
    </p>
  </div>
</footer>
<?php wp_footer(); ?>
</body>

</html>


