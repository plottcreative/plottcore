<?php

/**
 * Image settings
 *
 * @package PLOTT
 */

namespace PLOTT_THEME\Inc;

use PLOTT_THEME\Inc\Traits\Singleton;

class Images
{

	use Singleton;

	protected function __construct()
	{
		$this->setup_hooks();
	}

	private function setup_hooks(): void
	{
		add_filter('upload_mimes', [$this, 'svg_files']);
		add_filter('big_image_size_threshold', [$this, 'image_max_size'], 999, 1);
	}

	function svg_files($allowed) : array
	{
		if (!current_user_can('manage_options'))
			return $allowed;
		$allowed['svg'] = 'image/svg+xml';
		return $allowed;
	}

	function image_max_size($threshold) : int
	{
		return 2000;
	}

	function img_srcset($image_id, $class = '', $src_size = 'full', $img_sizes = '100vw') : string
	{

		$alt = '';
		$mime = '';
		$image_src = array();
		$src = '';
		$wp_sizes = array();
		$wp_size = '';
		$srcset_arr = array();
		$srcset = '';
		$image = '';

		if ($image_id) {

			$alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
			$alt = !empty($alt) ? $alt : '';

			$mime = get_post_mime_type($image_id);
			if ($mime === 'image/gif') { // GIFs require full image size for 'src' and no 'srcset'

				$image_src = wp_get_attachment_image_src($image_id, 'full');
				$src = $image_src[0];

			} elseif ($mime === 'image/jpeg' || $mime === 'image/png') { // JPGs and PNGs allowed 'srcset'

				$image_src = wp_get_attachment_image_src($image_id, $src_size);
				$src = $image_src[0];

				$wp_sizes = get_intermediate_image_sizes();
				foreach ($wp_sizes as $wp_size) {

					$size_src = wp_get_attachment_image_src($image_id, $wp_size);
					if (!empty($size_src)) {

						$width = $size_src[1];
						$url = $size_src[0];
						$srcset_arr[$width] = $url;

					}
				}

				if (!empty($srcset_arr)) {

					ksort($srcset_arr);
					foreach ($srcset_arr as $width => $size) {

						$srcset .= $size . ' ' . $width . 'w, ';

					}
				}

				$srcset = !empty($srcset) ? ' srcset="' . trim($srcset, ', ') . '"' . ' sizes="' . $img_sizes . '"' : '';

			}
			/**
			 * Added the loading="lazy" tag, this will allow the browser to either load the image or defer
			 * the loading of off-screen images until the user scrolls near them
			 */
			$image = !empty($src) ? '<img loading="lazy" class="img-fluid ' . $class . '" alt="' . $alt . '"' . $srcset . ' src="' . $src . '">' : '';

		}

		return $image;

	}

}
