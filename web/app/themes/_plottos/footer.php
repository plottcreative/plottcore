</main>
<footer class="main-footer" role="contentinfo">
	<div class="container">
		<div class="row">
			<?php
			wp_nav_menu(array(
				'theme_location' => 'footer-menu',
				'depth' => 2, // 1 = no dropdowns, 2 = with dropdowns.
				'container' => 'nav',
				'container_class' => 'main-footer__nav-container',
				'container_id' => 'main-footer__nav',
				'menu_class' => '',
				'fallback_cb' => '__return_false',
				'walker' => \PLOTT_THEME\Inc\PLOTT_Nav_Walker::get_instance(),
			));
			?>
		</div>
		<?php \PLOTT_THEME\Inc\Customizer::get_instance()->render_socials(); ?>
</footer>
<?php
?>

<?php \PLOTT_THEME\Inc\Load_Template::get_instance()->render('templates/parts/mobile-menu'); ?>
<?php wp_footer(); ?>
</body>
</html>
