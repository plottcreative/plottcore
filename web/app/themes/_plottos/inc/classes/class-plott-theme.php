<?php

/**
 * Bootstraps the Theme.
 *
 * @package PLOTTY
 */

namespace PLOTT_THEME\Inc;

use PLOTT_THEME\Inc\Traits\Singleton;

class PLOTT_THEME
{

	use Singleton;

	protected function __construct()
	{

		ACF::get_instance();
		Archive_Settings::get_instance();
		Assets::get_instance();
		Backward_Compact::get_instance();
		Comments::get_instance();
		Customizer::get_instance();
		Gravity_Forms::get_instance();
		Head::get_instance();
		Images::get_instance();
		Load_Template::get_instance();
		Menus::get_instance();
		Pagination::get_instance();
		Page_Setup::get_instance();
		PLOTT_Nav_Walker::get_instance();
		Tiny_Mce::get_instance();
		User_Role::get_instance();

		$this->setup_hooks();

	}

	protected function setup_hooks(): void
	{
		/**
		 * Actions.
		 */
		add_action('after_theme_setup', [$this, 'setup_theme']);
		add_action('after_switch_theme', [$this, 'plugin_activation']);
		self::theme_cpt();
		self::theme_tax();

	}

	static function theme_cpt() : void
	{
		CPT_Builder::create('Book', 'dashicons-book-alt');
		CPT_Builder::create('Movie', 'dashicons-video-alt');
	}

	static function theme_tax() : void
	{
		Taxonomy_Builder::create('Year', ['movie', 'book']);
		Taxonomy_Builder::create('Author', ['book']);
		Taxonomy_Builder::create('Genre', ['movie', 'post']);
	}

	/**
	 * Setup theme
	 *
	 * @return void
	 */
	public function setup_theme(): void
	{

		/**
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect Wordpress to
		 * provide it for us.
		 */
		add_theme_support('title-tag');

		/**
		 * Enable support for Post Thumbnails on post and pages.
		 *
		 * Adding this will allow you to select the featured image on posts and page.
		 *
		 * @link https://developer.wordpress.org/themes/functionallity/featured-images-post-thumbnails/
		 */
		add_theme_support('post-thumbnails');

		load_theme_textdomain('plott');

		add_theme_support('menus');

		/**
		 * Register image sizes.
		 */
		add_image_size('featured-thumbnail', 350, 233, true);

		/**
		 * Switch default core markup for search from, comment form, comment-list, gallery, caption, script and style
		 * to outpost valid HTML5
		 */
		add_theme_support(
			'html5',
			[
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'script',
				'style',
			]
		);

		/**
		 * Set the maximum allowed with for any content in the theme,
		 * like oEmbeds and images added to posts
		 *
		 * @see Content Width
		 * @link https://codex.wordpress.org/Content_Width
		 */
		global $content_width;
		if (!isset($content_width)) {
			$content_width = 1560;
		}

	}

	function plugin_activation(): void
	{
		$current = get_option('active_plugins');
		$plugin_array = [
			array('name' => 'gravityforms', 'file' => 'gravityforms'),
			array('name' => 'advanced-custom-fields-pro', 'file' => 'acf'),
			array('name' => 'amazon-s3-and-cloudfront', 'file' => 'wordpress-s3'),
			array('name' => 'ewww-image-optimizer', 'file' => 'ewww-image-optimizer'),
		];

		foreach ($plugin_array as $item) {
			$plugin = plugin_basename(trim($item['name'] . '/' . $item['file'] . '.php'));
			if (!in_array($plugin, $current)) {
				$current[] = $plugin;
				sort($current);
				do_action('activate_plugin', trim($plugin));
				update_option('active_plugins', $current);
				do_action('activate_' . trim($plugin));
				do_action('activated_plugin', trim($plugin));
			}
		}
	}


}
