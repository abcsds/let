<?php if (is_page()): the_post() ?>
	<section id="page-<?php the_ID() ?>">
		<?php the_content(); ?>
	</section>
<?php else:
	query_posts( array(
		'category_name' => 'landing',
		'posts_per_page' => -1,
		'order' => 'DESC'
	) );

	while (have_posts()) : the_post();
		the_content();
	endwhile;
endif; ?>
