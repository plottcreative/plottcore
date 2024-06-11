<?php

namespace PLOTT_THEME\Inc;

use PLOTT_THEME\Inc\Traits\Singleton;

if (class_exists('WooCommerce')) {

	class Woo
	{

		use singleton;


		protected function __construct()
		{
			$this->setup_hooks();
		}

		protected function setup_hooks(): void
		{

			// Remove Actions
			remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
			remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

			// Add Actions
			add_action('after_setup_theme', [$this, 'woo_setup']);

		}

		function plott_woo_setup(): void
		{
			add_theme_support('woocommerce',
				[
					'thumbnail_image_width' => 400,
					'single_image_width' => 800,
					'product_grid' => [
						'default_row' => 3,
						'min_rows' => 1,
						'default_columns' => 4,
						'min_columns' => 1,
						'max_columns' => 6,
					],
				]
			);

			if (get_theme_mod('gallery-zoom') === 'yes') add_theme_support('wc-product-gallery-zoom');
			if (get_theme_mod('gallery-slider') === 'yes') add_theme_support('wc-product-gallery-slider');
			if (get_theme_mod('gallery-lightbox') === 'yes') add_theme_support('wc-product-gallery-lightbox');
		}

	}
}
