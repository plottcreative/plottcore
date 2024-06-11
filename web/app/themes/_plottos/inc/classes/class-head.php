<?php

/**
 * Head Settings
 *
 * @package PLOTT
 */

namespace PLOTT_THEME\Inc;

use PLOTT_THEME\Inc\Traits\Singleton;

class Head
{

	use Singleton;

	protected function __construct()
	{
		$this->setup_hooks();
	}

	protected function setup_hooks(): void
	{
		/**
		 * Filters
		 */
		add_filter( 'use_block_editor_for_post', '__return_false' );
		add_filter('use_widgets_block_editor', '__return_false');
		add_filter('xmlrpc_enabled', '__return_false');
		add_filter('show_admin_bar', '__return_false');
		add_filter('tiny_mce_plugins', [$this, 'disable_emojis_tinymce']);

		/**
		 * Actions
		 */
		remove_action('wp_head', 'wp_generator');
		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'reset_output_link_wp_head', 10);
		remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
		remove_action('template_redirect', 'rest_output_link_header', 11, 0);
		remove_action('wp_head', 'wlwmanifest_link');
		remove_action('wp_head', 'wp_shortlink_wp_head');
		remove_action('wp_head', 'feed_links', 2);
		remove_action('wp_head', 'feed_links_extra', 3);

		add_action('init', [$this, 'disable_emojis']);

	}

	function disable_emojis(): void
	{
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('admin_print_scripts', 'print_emoji_detection_script');
		remove_action('wp_print_styles', 'print_emoji_styles');
		remove_action('admin_print_styles', 'print_emoji_styles');
		remove_filter('the_content_feed', 'wp_staticize_emoji');
		remove_filter('content_text_rss', 'wp_staticize_emoji');
		remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	}

	function disable_emojis_tinymce($plugins): array
	{
		if (is_array($plugins)) {
			return array_diff($plugins, array('wpemoji'));
		} else {
			return array();
		}
	}

}
