<?php if (is_page()): the_post() ?>
	<section id="page-<?php the_ID() ?>">
		<?php the_content(); ?>
	</section>
<?php else:
	$args = array(
		'sort_order' => 'ASC',
		'hierarchical' => 1,
		'exclude' => '',
		'child_of' => 0,
		'parent' => -1,
		'exclude_tree' => '',
		'number' => '',
		'offset' => 0,
		'post_type' => 'page',
		'post_status' => 'publish'
	);
	$pages = get_pages($args);
	foreach ($pages as $page_data) {
		$content = apply_filters('the_content', $page_data->post_content);
		$title = $page_data->post_title;
		$slug = $page_data->post_name;
	?>
	<div id='<?php echo "$slug" ?>'>
				<?php echo "$content" ?>
	</div>

	<?php
	}
endif; ?>
