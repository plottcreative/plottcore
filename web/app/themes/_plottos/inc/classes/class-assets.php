<?php

/**
 * Enqueue theme assets
 *
 * @package PLOTT
 */

namespace PLOTT_THEME\Inc;

use PLOTT_THEME\Inc\Traits\Singleton;

class Assets
{

	use Singleton;

	protected function __construct()
	{
		//load class.
		$this->setup_hooks();
	}

	protected function setup_hooks() : void
	{

		/**
		 * Actions.
		 */
		add_action('wp_enqueue_scripts', [self::class, 'register_styles']);
		add_action('wp_enqueue_scripts', [self::class, 'register_scripts']);
		add_action('wp_enqueue_scripts', [self::class, 'remove_scripts'], 100);

		/**
		 * Filters.
		 */
		add_filter( 'login_display_language_dropdown', '__return_false' );
		add_filter('login_enqueue_scripts', [self::class, 'login_styles']);

	}

	static function register_styles() : void
	{
		if ('production' !== wp_get_environment_type()) {
			wp_enqueue_style('theme-style', PLOTT_DIST_CSS_URI . '/style.css', '', filemtime(PLOTT_DIST_CSS_DIR_PATH . '/style.css'), 'all');
		} else {
			wp_enqueue_style('theme-style', PLOTT_DIST_CSS_URI . '/style.min.css', '', filemtime(PLOTT_DIST_CSS_DIR_PATH . '/style.min.css'), 'all');
		}
	}

	static function register_scripts() : void
	{
		wp_enqueue_script( 'jquery' );

		if ('production' !== wp_get_environment_type()) {
			wp_enqueue_script('theme', PLOTT_DIST_JS_URI . '/app.js', '', filemtime(PLOTT_DIST_JS_DIR_PATH . '/app.js'), true);
		} else {
			wp_enqueue_script('theme', PLOTT_DIST_JS_URI . '/app.min.js', '', filemtime(PLOTT_DIST_JS_DIR_PATH . '/app.min.js'), true);
		}
	}


	static function remove_scripts(): void
	{
		wp_dequeue_style('wp-block-library');
		wp_dequeue_style('global-styles-inline');
	}

	static function login_styles() : void
	{
		wp_enqueue_style( 'plott-login', PLOTT_DIR_URI . '/inc/admin/admin.css' );
	}


}
