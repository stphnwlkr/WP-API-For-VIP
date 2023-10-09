<?php

// Ensure the plugin is only executed within WordPress.

if (!defined('ABSPATH')) {
    exit;
}

function fetch_custom_taxonomy_terms($atts) {
    $a = shortcode_atts(array(
        'endpoint'       => '',
        'page_length'    => 50,
        'taxonomy_name'  => '' ,
    ), $atts);

    if (!$a['endpoint'] || !$a['taxonomy_name']) return 'Endpoint or taxonomy name missing!';

    $page_length = min(intval($a['page_length']), 100); // Ensure the maximum value is 100

    $current_page = isset($_GET['page_num']) ? intval($_GET['page_num']) : 1;

    $response = vip_safe_wp_remote_get($a['endpoint'] . '/wp-json/wp/v2/' . $a['taxonomy_name'] . '?per_page=' . $page_length . '&page=' . $current_page);

    if (is_wp_error($response)) return 'Error fetching custom taxonomy terms.';

    $terms = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($terms) || isset($terms['code'])) return 'No terms found for this taxonomy.';

    $output = '<ol>';
    foreach ($terms as $term) {
        $output .= '<li>' . esc_html($term['name']) . ' (ID: ' . esc_html($term['id']) . ')</li>';
    }
    $output .= '</ol>';

    // Pagination logic
    $total_terms = intval(wp_remote_retrieve_header($response, 'x-wp-total'));
    $total_pages = intval(wp_remote_retrieve_header($response, 'x-wp-totalpages'));

    if ($total_terms > $page_length) {
        $base_url = get_permalink();
        $output .= '<nav class="pagination"><ul>';
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $current_page) {
                $output .= '<li><span>' . $i . '</span></li>';
            } else {
                $output .= '<li><a href="' . esc_url(add_query_arg('page_num', $i, $base_url)) . '">' . $i . '</a></li>';
            }
        }
        $output .= '<ul></nav>';
    }

    return $output;
}

add_shortcode('fetch_taxonomy_terms', 'fetch_custom_taxonomy_terms');