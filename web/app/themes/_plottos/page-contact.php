<?php get_header(); //Template Name: Contact Page ?>

<section class="contact-form">
	<div class="contact-container container">
		<div class="gravity-form__wrapper">
			<?php echo do_shortcode('[gravityform id=2 title=false description=false ajax=true tabindex=49]'); ?>
		</div>
	</div>
</section>

<?php get_footer(); ?>
