<?php
/*
* Plugin Name: WP API Articles
* Description: Retrieves the latest articles from a specified WordPress API endpoint.
* Version: 2.0
* Plugin URI: https://github.com/stphnwlkr/WP-API-Plugin
* Requires at least: 6.0
* Requires PHP:      8.0
* Author: Stephen Walker
*/

// Ensure the plugin is only executed within WordPress.
if (!defined('ABSPATH')) {
    exit;
}

require plugin_dir_path(__FILE__) . 'shortcodes/api-articles.php';
require plugin_dir_path(__FILE__) . 'shortcodes/api-categories.php';
require plugin_dir_path(__FILE__) . 'shortcodes/api-get-custom-taxonomies.php';
require plugin_dir_path(__FILE__) . 'shortcodes/api-get-custom-terms.php';
require plugin_dir_path(__FILE__) . 'shortcodes/api-get-post-types.php';
require plugin_dir_path(__FILE__) . 'shortcodes/api-get-tags.php';