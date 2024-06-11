<?php
/**
 * Theme Functions
 *
 * @package PLOTT
 */

if (!defined('PLOTT_DIR_PATH')) {
	define('PLOTT_DIR_PATH', untrailingslashit(get_template_directory()));
}

if (!defined('PLOTT_DIR_URI')) {
	define('PLOTT_DIR_URI', untrailingslashit(get_template_directory_uri()));
}

if (!defined('PLOTT_DIST_PATH')) {
	define('PLOTT_DIST_PATH', untrailingslashit(get_template_directory() . '/assets/dist'));
}

if (!defined('PLOTT_DIST_URI')) {
	define('PLOTT_DIST_URI', untrailingslashit(get_template_directory_uri() . '/assets/dist'));
}

if (!defined('PLOTT_DIST_JS_DIR_PATH')) {
	define('PLOTT_DIST_JS_DIR_PATH', untrailingslashit(get_template_directory() . '/assets/dist/js'));
}

if (!defined('PLOTT_DIST_JS_URI')) {
	define('PLOTT_DIST_JS_URI', untrailingslashit(get_template_directory_uri() . '/assets/dist/js'));
}

if (!defined('PLOTT_DIST_IMG_PATH')) {
	define('PLOTT_DIST_IMG_URI', untrailingslashit(get_template_directory() . '/assets/dist/img'));
}

if (!defined('PLOTT_DIST_IMG_URI')) {
	define('PLOTT_DIST_IMG_URI', untrailingslashit(get_template_directory_uri() . '/assets/dist/img'));
}

if (!defined('PLOTT_DIST_CSS_DIR_PATH')) {
	define('PLOTT_DIST_CSS_DIR_PATH', untrailingslashit(get_template_directory() . '/assets/dist/css'));
}

if (!defined('PLOTT_DIST_CSS_URI')) {
	define('PLOTT_DIST_CSS_URI', untrailingslashit(get_template_directory_uri() . '/assets/dist/css'));
}

if (!defined('PLOTT_ARCHIVE_POST_PER_PAGE')) {
	define('PLOTT_ARCHIVE_POST_PER_PAGE', 1);
}

if (!defined('PLOTT_SEARCH_RESULTS_PER_PAGE')) {
	define('PLOTT_SEARCH_RESULTS_PER_PAGE', 9);
}

if (!defined('PLOTT_GOOGLE_MAPS_KEY')) {
	define('PLOTT_GOOGLE_MAPS_KEY', 'AIzaSyDg95BjfdBng96rNtHXwnkclvfkk7THDeo');
}

require_once PLOTT_DIR_PATH . '/inc/helpers/autoloader.php';

\PLOTT_THEME\Inc\PLOTT_THEME::get_instance();



