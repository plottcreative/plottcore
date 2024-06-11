<?php
$menu = \PLOTT_THEME\Inc\PLOTT_Nav_Walker::get_instance();
?>
<div id="mobile-menu">
	<div class="container">
		<?php
		wp_nav_menu(array(
			'theme_location' => 'primary-menu',
			'depth' => 2, // 1 = no dropdowns, 2 = with dropdowns.
			'container' => 'nav',
			'container_class' => 'mobile-menu__nav-container',
			'container_id' => 'mobile-menu__nav',
			'menu_class' => '',
			'fallback_cb' => '__return_false',
			'walker' => $menu,
		));
		?>
	</div>
</div>
