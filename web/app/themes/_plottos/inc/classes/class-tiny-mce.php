<?php
/**
 * Tiny MCE settings
 *
 * @package PLOTT
 */

namespace PLOTT_THEME\Inc;

use PLOTT_THEME\Inc\Traits\Singleton;

class Tiny_Mce
{

	use Singleton;

	protected function __construct()
	{
		$this->setup_hooks();
	}

	protected function setup_hooks() : void
	{
		/**
		 * Filters
		 */
		add_filter('tiny_mce_before_init', [$this, 'my_format_TinyMCE']);
		add_filter('the_content', [$this, 'remove_span_tags']);
		add_filter('the_excerpt', [$this, 'remove_span_tags']);
	}

	function my_format_TinyMCE($in)
	{
		$in['block_formats'] = "Body Text=p;Large Subheading=h2;Small Subheading=h3";
		return $in;
	}

	public function remove_span_tags($content)
	{
		$pattern = '/<span[^>]*>([^<]*)<\/span>/i'; // Regular expression pattern
		$replacement = '$1';
		return preg_replace($pattern, $replacement, $content);
	}

}
