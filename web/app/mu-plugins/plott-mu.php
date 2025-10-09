<?php

/*
 * Plugin Name: PLOTT MU-Plugins Loader
 * Description: Loads the Advanced Custom Fields plugin from the mu-plugins directory.
 * Author: Ashley Armstrong
 * Version: 1.0
 */

require_once WPMU_PLUGIN_DIR . '/advanced-custom-fields-pro/acf.php';
require_once WPMU_PLUGIN_DIR . '/gravityforms/gravityforms.php';
require_once WPMU_PLUGIN_DIR . '/amazon-s3-and-cloudfront/wordpress-s3.php';
require_once WPMU_PLUGIN_DIR . '/ewww-image-optimizer/ewww-image-optimizer.php';
require_once WPMU_PLUGIN_DIR . '/plott-gf/plott-gf.php';

if(WP_ENV === 'production' || WP_ENV === 'PRODUCTION' || WP_ENV === 'Production'){
    require_once WPMU_PLUGIN_DIR . '/cleantalk-spam-protect/cleantalk.php';
    require_once WPMU_PLUGIN_DIR . '/wp-super-cache/wp-cache.php';
}
