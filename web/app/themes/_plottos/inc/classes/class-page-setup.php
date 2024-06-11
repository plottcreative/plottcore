<?php
/**
 * Page Setup
 *
 * @package PLOTT
 */

namespace PLOTT_THEME\Inc;

use PLOTT_THEME\Inc\Traits\Singleton;

class Page_Setup
{

	use Singleton;

	protected function __construct()
	{
		$this->setup_hooks();
	}

	protected function setup_hooks(): void
	{
		add_action('after_switch_theme', [$this, 'add_terms_page']);
		add_action('after_switch_theme', [$this, 'add_cookies_page']);
		add_action('after_switch_theme', [$this, 'add_privacy_page']);
		add_action('after_switch_theme', [$this, 'add_front_page']);
		add_action('after_switch_theme', [$this, 'add_contact_page']);
	}

	function add_terms_page(): void
	{
		$pageSlug = 'terms';
		$page = array(
			'post_title' => 'Terms',
			'post_content' => '',
			'post_status' => 'publish',
			'post_author' => 1,
			'post_type' => 'page'
		);

		if (!get_page_by_path($pageSlug, OBJECT, 'page'))
			wp_insert_post($page);

	}

	function add_cookies_page(): void
	{
		$pageSlug = 'cookies';
		$page = array(
			'post_title' => 'Cookies',
			'post_content' => '',
			'post_status' => 'publish',
			'post_author' => 1,
			'post_type' => 'page'
		);

		if (!get_page_by_path($pageSlug, OBJECT, 'page'))
			wp_insert_post($page);

	}

	function add_privacy_page(): void
	{
		$pageSlug = 'privacy';
		$page = array(
			'post_title' => 'Privacy',
			'post_content' => '',
			'post_status' => 'publish',
			'post_author' => 1,
			'post_type' => 'page'
		);

		if (!get_page_by_path($pageSlug, OBJECT, 'page'))
			wp_insert_post($page);

	}

	function add_front_page(): void
	{
		$pageSlug = 'home';
		$page = array(
			'post_title' => 'Home',
			'post_content' => '',
			'post_status' => 'publish',
			'post_author' => 1,
			'post_type' => 'page'
		);

		if (!get_page_by_path($pageSlug, OBJECT, 'page'))
			wp_insert_post($page);

	}

	function add_contact_page(): void
	{
		$pageSlug = 'contact';
		$page = array(
			'post_title' => 'Contact',
			'post_content' => '',
			'post_status' => 'publish',
			'post_author' => 1,
			'post_type' => 'page'
		);

		if (!get_page_by_path($pageSlug, OBJECT, 'page'))
			wp_insert_post($page);

	}

}
