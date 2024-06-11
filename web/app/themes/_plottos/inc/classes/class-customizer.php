<?php

/**
 * Contact details in customizer
 *
 * @package PLOTT
 */

namespace PLOTT_THEME\Inc;

use PLOTT_THEME\Inc\Traits\Singleton;

class Customizer
{

	use Singleton;

	protected function __construct()
	{
		$this->setup_hooks();
	}

	protected function setup_hooks(): void
	{
		add_action('customize_register', [$this, 'customizer_remove']);
		add_action('customize_register', [$this, 'customizer_contact_details_register']);
		add_action('customize_register', [$this, 'customizer_google_tag_manager_register']);
		add_action('customize_register', [$this, 'customizer_site_branding_register']);
		add_action('customize_register', [$this, 'customizer_social_links_register']);
		if(class_exists('WooCommerce')){
			add_action('customize_register', [$this, 'customizer_woo_register']);
		}
	}

	public array $socials = [
		'youtube' => 'YouTube',
		'facebook' => 'Facebook',
		'twitter' => 'X/ Twitter',
		'instagram' => 'Instagram',
		'linkedin' => 'LinkedIn',
	];

	function customizer_remove($customizer)
	{

		$customizer->remove_section('custom_css');
		$customizer->remove_section('static_front_page');

		if (isset($customizer->nav_menus) && is_object($customizer->nav_menus)) {
			remove_filter('customize_refresh_nonces', array($customizer->nav_menus, 'filter_nonces'));
			remove_action('wp_ajax_load-available-menu-items-customizer', array($customizer->nav_menus, 'ajax_load_available_items'));
			remove_action('wp_ajax_search-available-menu-items-customizer', array($customizer->nav_menus, 'ajax_search_available_items'));
			remove_action('customize_controls_enqueue_scripts', array($customizer->nav_menus, 'enqueue_scripts'));
			remove_action('customize_register', array($customizer->nav_menus, 'customize_register'), 11);
			remove_filter('customize_dynamic_setting_args', array($customizer->nav_menus, 'filter_dynamic_setting_args'), 10, 2);
			remove_filter('customize_dynamic_setting_class', array($customizer->nav_menus, 'filter_dynamic_setting_class'), 10, 3);
			remove_action('customize_controls_print_footer_scripts', array($customizer->nav_menus, 'print_templates'));
			remove_action('customize_controls_print_footer_scripts', array($customizer->nav_menus, 'available_items_template'));
			remove_action('customize_preview_init', array($customizer->nav_menus, 'customize_preview_init'));
			remove_filter('customize_dynamic_partial_args', array($customizer->nav_menus, 'customize_dynamic_partial_args'), 10, 2);
		}
//
		return $customizer;

	}

	function customizer_contact_details_register($customizer)
	{
		// Add Panel
		$customizer->add_section('contact-details',
			array(
				'title' => __('Contact Details', 'plott'),
				'priority' => 200,
			)
		);

		// Define Settings
		$customizer->add_setting('contact-address');
		$customizer->add_setting('contact-email');
		$customizer->add_setting('contact-phone');

		// Add Address Control
		$customizer->add_control(
			new \WP_Customize_Control(
				$customizer, 'contact-address', array(
					'label' => __('Contact Address', 'plott'),
					'section' => 'contact-details',
					'settings' => 'contact-address',
					'type' => 'textarea'
				)
			)
		);

		// Add Email Control
		$customizer->add_control(
			new \WP_Customize_Control(
				$customizer, 'contact-email', array(
					'label' => __('Contact Email', 'plott'),
					'section' => 'contact-details',
					'settings' => 'contact-email',
					'type' => 'text'
				)
			)
		);

		// Add Phone Control
		$customizer->add_control(
			new \WP_Customize_Control(
				$customizer, 'contact-phone', array(
					'label' => __('Contact Phone', 'plott'),
					'section' => 'contact-details',
					'settings' => 'contact-phone',
					'type' => 'text'
				)
			)
		);

		return $customizer;
	}

	function customizer_google_tag_manager_register($customizer)
	{

		$customizer->add_section('google-tag-manager',
			array(
				'title' => __('Google Tag Manager'),
				'priority' => 150,
			)
		);

		$customizer->add_setting('google-head');
		$customizer->add_setting('google-body');

		$customizer->add_control(
			new \WP_Customize_Control(
				$customizer, 'google-head', array(
					'label' => __('Google Head Tag', 'plott'),
					'section' => 'google-tag-manager',
					'settings' => 'google-head',
					'type' => 'textarea'
				)
			)
		);

		$customizer->add_control(
			new \WP_Customize_Control(
				$customizer, 'google-body', array(
					'label' => __('Google Body Tag', 'plott'),
					'section' => 'google-tag-manager',
					'settings' => 'google-body',
					'type' => 'textarea'
				)
			)
		);

		return $customizer;

	}

	function customizer_site_branding_register($customizer)
	{

		$customizer->add_setting('site-logo');
		$customizer->add_setting('site-footer-logo');

		$customizer->add_control(
			new \WP_Customize_Image_Control(
				$customizer, 'site-logo', array(
					'label' => __('Site Logo', 'plott'),
					'description' => __('This is a the main site logo', 'plott'),
					'section' => 'title_tagline',
					'settings' => 'site-logo'
				)
			)
		);

		$customizer->add_control(
			new \WP_Customize_Image_Control(
				$customizer, 'site-footer-logo', array(
					'label' => __('Footer Logo', 'plott'),
					'description' => __('This is a the footer logo', 'plott'),
					'section' => 'title_tagline',
					'settings' => 'site-footer-logo'
				)
			)
		);

		return $customizer;

	}

	function customizer_social_links_register($customizer)
	{

		$customizer->add_section('social-links',
			array(
				'title' => __('Social Links', 'plott'),
				'priority' => 150,
			)
		);

		foreach ($this->socials as $key => $name) {
			$customizer->add_setting('social-' . $key);
			$socialName = sprintf(__('%1$s URL', 'plott'), $name);
			$customizer->add_control(
				new \WP_Customize_Control(
					$customizer, 'social-' . $key, array(
						'label' 		=> sprintf(__('%1$s URL', 'plott'), $name),
						'description' 	=> sprintf(__('Insert the URL for your %1$s here', 'plott'), $name),
						'section' 		=> 'social-links',
						'settings' 		=> 'social-' . $key,
						'type' 			=> 'url'
					)
				)
			);
		}


		return $customizer;

	}

	function render_socials(): void
	{
		echo '<ul class="social-links">';
		foreach ($this->socials as $key => $value) {
			if(get_theme_mod('social-'.$key)){
				echo '
				<li class="social-links__link">
				<a href="'.get_theme_mod('social-'.$key).'" target="_blank" role="link" title="Follow us on '.$value.'">
							<img src="'. PLOTT_DIR_URI .'/assets/dist/img/socials/'. $key. '.svg" alt="">
						</a>
				</li>
				';
			}
		}
	}


	function customizer_woo_register($customizer)
	{

		$customizer->add_section('plott-woo',
			[
				'title' => 'PLOTT Woo Settings',
				'priority' => 150
			]
		);

		$customizer->add_setting('gallery-zoom');
		$customizer->add_setting('gallery-slider');
		$customizer->add_setting('gallery-lightbox');

		$customizer->add_control(
			new \WP_Customize_Control(
						$customizer, 'gallery-zoom',
				[
					'label' => __('Enable Gallery Zoom', 'plott'),
					'section' => 'plott-woo',
					'settings' => 'gallery-zoom',
					'type' => 'select',
					'choices' => [
						'yes' => __('Yes', 'plott'),
						'no' => __('No', 'plott'),
					]
				]
			)
		);

		$customizer->add_control(
			new \WP_Customize_Control(
				$customizer, 'gallery-slider',
				[
					'label' => __('Enable Gallery Slider', 'plott'),
					'section' => 'plott-woo',
					'settings' => 'gallery-slider',
					'type' => 'select',
					'choices' => [
						'yes' => __('Yes', 'plott'),
						'no' => __('No', 'plott'),
					]
				]
			)
		);

		$customizer->add_control(
			new \WP_Customize_Control(
				$customizer, 'gallery-lightbox',
				[
					'label' => __('Enable Gallery Lightbox', 'plott'),
					'section' => 'plott-woo',
					'settings' => 'gallery-lightbox',
					'type' => 'select',
					'choices' => [
						'yes' => __('Yes', 'plott'),
						'no' => __('No', 'plott'),
					]
				]
			)
		);

		return $customizer;
	}

}
