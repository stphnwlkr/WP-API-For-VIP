<?php

// Ensure the plugin is only executed within WordPress.

if (!defined('ABSPATH')) {
    exit;
}

function fetch_categories_from_endpoint($atts) {
    $a = shortcode_atts(array(
        'endpoint' => '',
    ), $atts);

    if (!$a['endpoint']) return 'No endpoint provided!';

    $paged = (isset($_GET['catpaged']) && $_GET['catpaged'] > 0) ? absint($_GET['catpaged']) : 1;
    $response = vip_safe_wp_remote_get($a['endpoint'] . '/wp-json/wp/v2/categories?per_page=100&page=' . $paged);

    if (is_wp_error($response)) return 'Error fetching categories.';

    $categories = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($categories)) return 'No categories found.';

    $output = '<ol>';
    foreach ($categories as $category) {
        $output .= '<li>' . esc_html($category['name']) . ' (' . intval($category['id']) . ')</li>';
    }
    $output .= '</ol>';

    // Pagination
    $total_pages = intval(wp_remote_retrieve_header($response, 'x-wp-totalpages'));
    $current_url = add_query_arg(null, null);
    $current_url = remove_query_arg('catpaged', $current_url);

    if ($paged > 1) {
        $output .= '<a href="' . esc_url(add_query_arg('catpaged', ($paged - 1), $current_url)) . '">Previous</a>';
    }
    if ($paged < $total_pages) {
        $output .= ' <a href="' . esc_url(add_query_arg('catpaged', ($paged + 1), $current_url)) . '">Next</a>';
    }

    return $output;
}

add_shortcode('fetch_categories', 'fetch_categories_from_endpoint');
