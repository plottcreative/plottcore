<?php

namespace PLOTT_THEME\Inc;

use PLOTT_THEME\Inc\Traits\Singleton;

class Backward_Compact
{

	use Singleton;

	protected function __construct()
	{
		$this->setup_hooks();
	}

	protected function setup_hooks() : void
	{
//		add_action('after_switch_theme', [$this,'plott_switch_theme']);
//		add_action('init', [$this, 'plott_switch_theme']);
//		add_action( 'template_redirect', [$this, 'plott_preview']);
//		add_action( 'load-customize.php', [$this,'plott_customize']);
	}

	function plott_switch_theme()
	{
		switch_theme(WP_DEFAULT_THEME);
		unset($_GET['activated']);
		add_action('after_switch_theme', [$this, 'plott_upgrade_notice']);
	}

	function plott_upgrade_notice()
	{
		printf(
			'<div class"error"><p>%s</p></div>',
			sprintf(
				__('PLOTT Base Theme requires at least WordPress version 5.9.3. You are running %s. Please upgrade and try again.', 'plott'),
				$GLOBALS['wp_version']
			)
		);
	}

	function plott_customize()
	{
		wp_die(
			sprintf(
				__('PLOTT Base Theme requires at least WordPress version 5.9.3. You are running %s. Please upgrade and try again.', 'plott'),
				$GLOBALS['wp_version']
			),
			'',
			array(
				'back_link'	=> true,
			)
		);
	}

	function plott_preview()
	{
		if ( isset( $_GET['preview'] ) ) {
			wp_die(
				sprintf(
					__('PLOTT Base Theme requires at least WordPress version 5.9.3. You are running %s. Please upgrade and try again.', 'plott'),
					$GLOBALS['wp_version']
				)
			);
		}
	}

}
