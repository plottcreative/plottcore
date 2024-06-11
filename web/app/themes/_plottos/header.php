<?php
/**
 * @package PLOTT
 */
$menu = \PLOTT_THEME\Inc\PLOTT_Nav_Walker::get_instance();
?>
<!doctype html>
<html lang="en">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php //plott_get_part('templates/parts/rotate'); ?>
<a class="skip-link screen-reader-text" href="#primary" role="link"><?php esc_html_e('Skip to content', 'plott'); ?></a>
<header class="main-header" role="banner">
	<div class="container main-header__container">
		<div class="row align-items-center">
			<div class="col-lg-4 col-6">
				<a href="<?php echo bloginfo('url'); ?>" title="<?php echo bloginfo('name'); ?>">
					<?php if (get_theme_mod('site-logo')) : ?>
						<img src="<?php echo get_theme_mod('site-logo'); ?>" alt="<?php echo bloginfo('name'); ?> logo"
							 class="main-header__logo">
					<?php else: ?>
						<h1 class="text-white"><?php echo bloginfo('name'); ?></h1>
					<?php endif; ?>
				</a>
			</div>
			<div class="col-lg-8 col-6">
				<div class="d-block d-xl-none">
					<button type="button" class="burger-menu" id="burger-menu" role="button">
						<div></div>
						<div></div>
						<div></div>
					</button>
				</div>
				<div class="d-none d-xl-block">
					<?php
					wp_nav_menu(array(
						'theme_location' => 'primary-menu',
						'depth' => 2, // 1 = no dropdowns, 2 = with dropdowns.
						'container' => 'nav',
						'container_class' => 'main-header__nav',
						'container_id' => 'main-header__nav',
						'menu_class' => '',
						'fallback_cb' => '__return_false',
						'walker' => $menu,
					));
					?>
				</div>
			</div>
		</div>
	</div>
</header>
<main id="primary" class="main" role="main">

