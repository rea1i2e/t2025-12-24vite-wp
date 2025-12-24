<?php

declare(strict_types=1);

// Data (keep logic separate from markup)
$site_name  = (string) get_bloginfo('name');
$page_title = (string) wp_get_document_title();
$home_url   = (string) home_url('/');

get_header();

?>
<main id="main" class="l-main">
	<header class="l-inner">
		<h1>
			<a href="<?php echo esc_url($home_url); ?>">
				<?php echo esc_html($site_name); ?>
			</a>
		</h1>
		<p><?php echo esc_html($page_title); ?></p>
	</header>

	<div class="l-inner">
		<?php
if (have_posts()) {
	while (have_posts()) {
		the_post();
		?>
		<article <?php post_class(); ?>>
			<?php the_content(); ?>
		</article>
		<?php
	}
}
		?>
	</div>
  <div class="test">background-imageのテスト</div>
</main>
<?php

get_footer();


