<?php
/**
 * Comments
 *
 * @package PLOTT
 */

namespace PLOTT_THEME\Inc;

use PLOTT_THEME\Inc\Traits\Singleton;

class Comments
{

	use Singleton;

	protected function __construct()
	{
		$this->setup_hooks();
	}

	protected function setup_hooks() : void
	{
		add_action('admin_menu', [$this, 'remove_comments_menu']);
		add_action('init', [$this, 'remove_comments_support'], 100);
		add_action('wp_before_admin_bar_render', [$this, 'remove_comments_admin_bar']);
	}

	function remove_comments_menu() : void
	{
		remove_menu_page('edit-comments.php');
	}

	function remove_comments_support() : void
	{
		remove_post_type_support('page', 'comments');
		remove_post_type_support('post', 'comments');
	}

	function remove_comments_admin_bar() : void
	{
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu('comments');
	}

}
