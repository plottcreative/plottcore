<?php

/**
 * Load Template Function
 *
 * @package PLOTT
 */

namespace PLOTT_THEME\Inc;

use PLOTT_THEME\Inc\Traits\Singleton;

class Load_Template
{

	use Singleton;

	protected function __construct()
	{
		$this->setup_hooks();
	}

	protected function setup_hooks(): void
	{
	}

	/**
	 * Get a template and safely pass variables into it.
	 *
	 * @param string $template Template path without extensions
	 * @param array $variables Variables to pass into the part
	 * @param false|array|string $cache Cache key as string or key & ttl in array
	 * @param boolean $echo Echo output when true, return when false
	 * @return boolean True on success, false on failure
	 * @throws \Exception
	 */

	function render(string $template, array $variables = array(), false|array|string $cache = false, bool $echo = true, $ttl = 0): bool
	{

		if (!is_array($variables))
			return false;

		if ($cache) {
			if (is_string($cache)) {
				$key = $cache;
				$ttl = DAY_IN_SECONDS;
			} elseif (is_array($cache)) {
				$key = $cache['key'] ?? null;
				$ttl = isset($cache['ttl']) ?? DAY_IN_SECONDS;
				$cache_users = $cache['users'] ?? true;
			}

			if (!$key)
				throw new \Exception('Invalid cache arguments supplied to ' . __FUNCTION__);

			$key = apply_filters('plott_part_fragment_prefix', 'plott_part_fragment') . $key;

			if (!is_user_logged_in() || $cache_users) {
				$output = get_transient($key);
				if (!empty($output)) {
					if ($echo) {
						echo $output;
						return true;
					} else {
						return $output;
					}
				}
			}
		}

		$template = locate_template($template . '.php');

		if (!$template)
			return false;

		foreach ($variables as $var => $val)
			$$var = $val;

		ob_start();

		include($template);

		$output = ob_get_clean();

		if (isset($key))
			set_transient($key, $output, $ttl);

		if ($echo) {
			echo $output;
			return true;
		} else {
			return $output;
		}

	}
}
