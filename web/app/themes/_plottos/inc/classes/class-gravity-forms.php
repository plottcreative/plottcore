<?php
/**
 * Gravity Forms
 *
 * @package PLOTT
 */

namespace PLOTT_THEME\Inc;

use PLOTT_THEME\Inc\Traits\Singleton;

class Gravity_Forms
{

	use Singleton;

	protected function __construct()
	{
		$this->setup_hooks();
	}

	protected function setup_hooks(): void
	{
		/**
		 * Actions
		 */
		add_action('wp_enqueue_scripts', [$this, 'remove_grav_styles']);

		/**
		 * Filters
		 */
		add_filter('gform_force_hooks_js_output', '__return_false');
		add_filter('gform_disable_form_theme_css', '__return_false');
		add_filter('gform_phone_formats', [$this, 'uk_phone_formats'], 10, 2);
		add_filter('gform_submit_button', [$this, 'add_custom_css_class'], 10, 2);
		add_filter('gform_submit_button', [$this, 'change_submit_btn_txt'], 10, 2);
		add_filter('gform_notification', [$this, 'change_from_email'], 10, 3);
	}

	function remove_grav_styles(): void
	{
		wp_dequeue_style('gforms_reset_css');
		wp_dequeue_style('gforms_datepicker_css');
		wp_dequeue_style('gforms_formsmain_css');
		wp_dequeue_style('gforms_ready_class_css');
		wp_dequeue_style('gforms_browsers_css');
	}

	function userRole(): void
	{
		$role = get_role('editor');

		$role->add_cap('gravityforms_view_entries');
	}

	function uk_phone_formats($phone_formats)
	{
		$phone_formats['uk'] = array(
			'label' => 'UK',
			'mask' => false,
			'regex' => '/^(((\+44\s?\d{4}|\(?0\d{4}\)?)\s?\d{3}\s?\d{3})|((\+44\s?\d{3}|\(?0\d{3}\)?)\s?\d{3}\s?\d{4})|((\+44\s?\d{2}|\(?0\d{2}\)?)\s?\d{4}\s?\d{4}))(\s?\#(\d{4}|\d{3}))?$/',
			'instruction' => false,
		);

		return $phone_formats;
	}

	function add_custom_css_class($button, $form)
	{
		$dom = new \DOMDocument();
		$dom->loadHTML('<?xml encoding="utf-8" ?>' . $button);
		$input = $dom->getElementsByTagName('input')->item(0);
		$classes = $input->getAttribute('class');
		$classes .= " btn btn-sm btn-secondary gravity-btn-contact";
		$input->setAttribute('class', $classes);
		return $dom->saveHtml($input);
	}

	function change_submit_btn_txt($button, $form)
	{
		$dom = new \DOMDocument();
		$dom->loadHTML('<?xml encoding="utf-8" ?>' . $button);
		$input = $dom->getElementsByTagName('input')->item(0);
		$onclick = $input->getAttribute('onclick');
		$result = \GFAPI::form_id_exists($form);
		$onclick .= " this.value='Submitting...'"; // Change button text when clicked.
		$input->setAttribute('onclick', $onclick);
		return $dom->saveHtml($input);
	}

	function change_from_email($notification, $form, $entry)
	{
		$notification['from'] = 'solo@razorcreations.email';
		return $notification;
	}

}
