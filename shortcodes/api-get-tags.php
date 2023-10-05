<?php

// Ensure the plugin is only executed within WordPress.

if (!defined('ABSPATH')) {
    exit;
}

function fetch_tags_from_endpoint($atts) {
    $a = shortcode_atts(array(
        'endpoint' => '',
    ), $atts);

    if (!$a['endpoint']) return 'No endpoint provided!';

    $paged = (isset($_GET['tagpaged']) && $_GET['tagpaged'] > 0) ? absint($_GET['tagpaged']) : 1;
    $response = vip_safe_wp_remote_get($a['endpoint'] . '/wp-json/wp/v2/tags?per_page=100&page=' . $paged);

    if (is_wp_error($response)) return 'Error fetching tags.';

    $tags = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($tags)) return 'No tags found.';

    $output = '<ol>';
    foreach ($tags as $tag) {
        $output .= '<li>' . esc_html($tag['name']) . ' (' . intval($tag['id']) . ')</li>';
    }
    $output .= '</ol>';

    // Pagination
    $total_pages = intval(wp_remote_retrieve_header($response, 'x-wp-totalpages'));
    $current_url = add_query_arg(null, null);
    $current_url = remove_query_arg('tagpaged', $current_url);

    if ($paged > 1) {
        $output .= '<a href="' . esc_url(add_query_arg('tagpaged', ($paged - 1), $current_url)) . '">Previous</a>';
    }
    if ($paged < $total_pages) {
        $output .= ' <a href="' . esc_url(add_query_arg('tagpaged', ($paged + 1), $current_url)) . '">Next</a>';
    }

    return $output;
}

add_shortcode('fetch_tags', 'fetch_tags_from_endpoint');