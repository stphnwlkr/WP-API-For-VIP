<?php

// Ensure the plugin is only executed within WordPress.

if (!defined('ABSPATH')) {
    exit;
}

function fetch_custom_taxonomies($atts) {
    $a = shortcode_atts(array(
        'endpoint' => '',
    ), $atts);

    if (!$a['endpoint']) return 'No endpoint provided!';

    $response = vip_safe_wp_remote_get($a['endpoint'] . '/wp-json/wp/v2/taxonomies');

    if (is_wp_error($response)) return 'Error fetching custom taxonomies.';

    $taxonomies = json_decode(wp_remote_retrieve_body($response), true);

    // Filter out built-in taxonomies
    $exclude_builtin = array('category', 'post_tag', 'nav_menu', 'link_category', 'post_format');
    $custom_taxonomies = array_diff_key($taxonomies, array_flip($exclude_builtin));

    if (empty($custom_taxonomies)) return 'No custom taxonomies found.';

    $output = '<ul>';
    foreach ($custom_taxonomies as $slug => $taxonomy) {
        $output .= '<li>' . esc_html($taxonomy['name']) . ' (' . esc_html($slug) . ')</li>';
    }
    $output .= '</ul>';

    return $output;
}

add_shortcode('fetch_taxonomies', 'fetch_custom_taxonomies');
