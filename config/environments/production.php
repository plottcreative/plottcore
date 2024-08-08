<?php
/**
 * Configuration overrides for WP_ENV === 'prooduction'
 */

use Plott\PlottcoreWpConfig\Config;
use function Env\env;

Config::define('WP_CONTENT_URL', Config::get('WP_HOME') . '/web' . Config::get('CONTENT_DIR'));