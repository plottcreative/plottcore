<?php get_header(); ?>
	<article class="blog__body">
		<?php
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				?>
				<div class="card">
					<h1><?php echo the_title(); ?></h1>
				</div>
		<?php
			} // end while
		} // end if
		?>
		<?php \PLOTT_THEME\Inc\Pagination::get_instance()->render(); ?>
	</article>
<?php get_footer(); ?>
