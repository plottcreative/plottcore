<?php
/**
 * ACF Settings
 *
 * @package PLOTT
 */

namespace PLOTT_THEME\Inc;

use PLOTT_THEME\Inc\Traits\Singleton;

class ACF
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
		add_filter('acf/settings/save_json', [$this, 'acf_save_point'], 1);
		add_filter('acf/settings/load_json', [$this, 'acf_load_point']);
		add_filter('acf/admin/license_key_constant_message', [$this, 'acf_license_key_constant']);
		add_filter('acf/settings/enable_post_types', '__return_false');
		add_filter('acf/settings/enable_options_pages_ui', '__return_false');
		add_filter('acf/settings/save_json', [$this, 'get_file_path']);


		/**
		 * Actions
		 */
		add_action('admin_menu', [$this, 'remove_acf_menu']);
		add_action('acf/init', [$this, 'google_maps_api_key']);
		add_action('admin_init', [$this, 'sync_acf_fields']);
		add_action('acf/init', [$this, 'acf_options_footer']);
		add_action('acf/update_field_group', [self::class, 'init_json']);

	}

	function acf_save_point($path)
	{
		return get_stylesheet_directory() . '/acf-json';
	}

	function acf_load_point($paths)
	{
		unset($paths[0]);

		$paths[] = get_stylesheet_directory() . '/acf-json';

		return $paths;
	}

	function acf_license_key_constant($message): string
	{
		return '<h4> Your ACF license key is provided by <a href="https://plott.co.uk" target="_blank">PLOTT</a></h4>';
	}

	function remove_acf_menu(): void
	{
		$admins = [
			'plott'
		];

		$currentUser = wp_get_current_user();

		if (!in_array($currentUser->user_login, $admins)) {
			remove_menu_page('edit.php?post_type=acf-field-group');
		}

	}

	function google_maps_api_key(): void
	{
		acf_update_setting('google_api_key', PLOTT_GOOGLE_MAPS_KEY);
	}

	function sync_acf_fields(): void
	{
		// Variables
		$groups = acf_get_field_groups();
		$sync = array();

		// Bail early if no field groups
		if (empty($groups))
			return;

		// Find JSON field groups which have not yet been imported
		foreach ($groups as $group) {

			// Variables
			$local = acf_maybe_get($group, 'local', false);
			$modified = acf_maybe_get($group, 'modified', 0);
			$private = acf_maybe_get($group, 'private', false);

			// Ignore DB, PHP and private field groups
			if ($local !== 'json' || $private) {
				// do nothing
			} elseif (!$group['ID']) {
				$sync[$group['key']] = $group;
			} elseif ($modified && $modified > get_post_modified_time('U', true, $group['ID'], true)) {
				$sync[$group['key']] = $group;
			}

			// Bail if no sync needed
			if (empty($sync))
				return;

			if (!empty($sync)) {
				// Variables
				$new_ids = array();

				foreach ($sync as $key => $v) {
					// Append fields
					if (acf_have_local_fields($key)) {
						$sync[$key]['fields'] = acf_get_local_fields($key);
					}

					// Import
					$field_group = acf_import_field_group($sync[$key]);
				}
			}

		}
	}

	static function create_layout_files_for_page_builder($path)
	{
		$json_dir = $path;
		$page_builder_key = 'group_623bb7b816840'; // The key of your "Page Builder" field group
		// Path to the Page Builder JSON file
		$file_path = $json_dir . '/' . $page_builder_key . '.json';

		// Initial check if the file exists
		if (!file_exists($file_path)) {
			return "File does not exist.";
		}

		$json_content = file_get_contents($file_path);
		$field_group = json_decode($json_content, true);

		// Check if it's the Page Builder field group
		if (isset($field_group['key']) && $field_group['key'] === $page_builder_key) {
			foreach ($field_group['fields'] as $field) {
				if ($field['name'] === 'page_builder' && isset($field['layouts'])) {
					foreach ($field['layouts'] as $layout) {
						$layout_name = sanitize_title_with_dashes(str_replace('_', '-', $layout['name']));
						$dir_path = get_template_directory() . '/templates/blocks/' . $layout_name;

						if (!file_exists($dir_path)) {
							mkdir($dir_path);

							$files_to_create = ['js', 'php', 'scss'];
							foreach ($files_to_create as $ext) {
								// Add underscore for SCSS files
								$filename = ($ext === 'scss') ? '_' . $layout_name : $layout_name;
								$file_path = $dir_path . '/' . $filename . '.' . $ext;

								if (!file_exists($file_path)) {
									file_put_contents($file_path, "// File for layout $layout_name\n");
								}
							}

						}
					}
				}
			}
		}

		return $path;
	}

	function get_file_path($path)
	{
		$custom_path = get_stylesheet_directory() . '/acf-json';
		return $custom_path;
	}

	static function init_json($field_group)
	{
		$path = get_stylesheet_directory() . '/acf-json'; // Path where ACF saves its JSON files
		self::create_layout_files_for_page_builder($path);
	}

	function acf_options_footer(): void
	{
		if (function_exists('acf_add_options_page')) {
			$option_page = acf_add_options_page(array(
				'page_title' => __('Footer Settings'),
				'menu_title' => __('Footer Settings'),
				'menu_slug' => 'footer-settings',
				'capability' => 'edit_posts',
				'redirect' => false,
			));
		}
	}

}
