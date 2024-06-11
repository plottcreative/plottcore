<?php get_header();
	/* Template Name: Page Builder */
	global $post;
?>

<article class="page-builder page-<?php echo sanitize_title_with_dashes( get_the_title( $post->ID ) ); ?>"
         role="article">
	<?php
		\PLOTT_THEME\Inc\Load_Template::get_instance()->render( 'templates/parts/acf-fields' );
	?>
</article>

<?php get_footer(); ?>
