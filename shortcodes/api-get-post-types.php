<?php

// Ensure the plugin is only executed within WordPress.

if (!defined('ABSPATH')) {
    exit;
}

function fetch_custom_post_types($atts) {
    $a = shortcode_atts(array(
        'endpoint' => '',
    ), $atts);

    if (!$a['endpoint']) return 'No endpoint provided!';

    $response = vip_safe_wp_remote_get($a['endpoint'] . '/wp-json/wp/v2/types');

    if (is_wp_error($response)) return 'Error fetching custom post types.';

    $types = json_decode(wp_remote_retrieve_body($response), true);
    
    // Filter out built-in post types
    $exclude_builtin = array('post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block');
    $custom_types = array_diff_key($types, array_flip($exclude_builtin));

    if (empty($custom_types)) return 'No custom post types found.';

    $output = '<ul>';
    foreach ($custom_types as $slug => $type) {
        $output .= '<li>' . esc_html($type['name']) . ' (' . esc_html($slug) . ')</li>';
    }
    $output .= '</ul>';

    return $output;
}

add_shortcode('fetch_cpts', 'fetch_custom_post_types');
