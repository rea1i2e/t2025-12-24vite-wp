<?php

declare(strict_types=1);
?>

<footer class="p-footer l-footer" id="footer">
	<div class="p-footer__inner l-inner">
		<div class="p-footer__nav">
			<?php
			wp_nav_menu([
				'theme_location' => 'footer',
				'container'      => false,
				'menu_class'     => 'p-footer__nav-list',
				'fallback_cb'    => false,
				'depth'          => 1,
			]);
			?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>


