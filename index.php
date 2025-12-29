<?php get_header(); ?>
<main class="p-main-sub">
	<?php get_template_part('components/breadcrumb') ?>
	<h1><?php the_title(); ?></h1>
	<?php the_content(); ?>
</main>
<?php get_footer(); ?>