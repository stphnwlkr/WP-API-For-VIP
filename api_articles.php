<?php
/*
* Plugin Name: WP API Articles
* Description: Retrieves the latest articles from a specified WordPress API endpoint.
* Version: 1.8
* Plugin URI: https://github.com/stphnwlkr/WP-API-Plugin
* Requires at least: 6.0
* Requires PHP:      8.0
* Author: Stephen Walker
*/

// Ensure the plugin is only executed within WordPress.
if (!defined('ABSPATH')) {
    exit;
}

// Get articles

function api_articles_shortcode($atts) {
   
    $args = shortcode_atts(array(
        'endpoint' => '',
        'count'=> 6,
        'offset' => 0,
        'format_date' => 'F d, Y',
        'post_type' => 'posts',
        'show_excerpt' => 'yes',
        'show_date' => 'yes',
        'show_category' => 'yes',
        'category' => '',
        'cat_exclude' => '',
        'tag' => '',
        'heading_level' => 'h2',
        'show_img' => 'yes',
        'article_class' => '',
		'date_format_type' => 'human',
        'post_slug' => '',
        'link_target' => '',  // e.g. '_blank' for a new tab
        'link_aria_label' => '',
    ), $atts);

    $args = array_map('sanitize_text_field', $args);
    $args['count'] = absint($args['count']);
    $args['offset'] = absint($args['offset']);
    $args['article_class'] = sanitize_html_class($args['article_class']);

    if (empty($args['endpoint'])) {
        return 'Error: Please specify the API endpoint.';
    }

    $category_query = '';
    if (!empty($args['category'])) {
        $category_query = "&categories={$args['category']}";
    }
    $category_exclude_query = '';
    if (!empty($args['cat_exclude'])) {
        $category_exclude_query = "&categories_exclude={$args['cat_exclude']}";
    }
    $tag_query = '';
    if (!empty($args['tag'])) {
        $tag_query = "&tags={$args['tag']}";
    }

    if (!empty($args['post_slug'])) {
        $response = vip_safe_wp_remote_get("{$args['endpoint']}/wp-json/wp/v2/{$args['post_type']}?_embed&slug={$args['post_slug']}");
    } else {
        $response = vip_safe_wp_remote_get("{$args['endpoint']}/wp-json/wp/v2/{$args['post_type']}?_embed&per_page={$args['count']}&offset={$args['offset']}{$category_query}{$category_exclude_query}{$tag_query}");
    }

    if (is_wp_error($response)) {
        error_log($response->get_error_message());
        return 'Error: ' . $response->get_error_message();
    }

    $response_code = wp_remote_retrieve_response_code($response);

    if ($response_code != 200) {
        return 'There is a problem and we are now working on it. HTTP Response Code: ' . $response_code;
    }

    $posts = wp_remote_retrieve_body($response);
    $posts = json_decode($posts, true);

    if (empty($posts)) {
        return 'No articles are available.';
    }

    // Generate the appropriate HTML tag for the heading
    $heading = $args['heading_level'];

    $output = '<ul class="api-articles ' . esc_attr($args['article_class']) .'">';

    foreach ($posts as $post) {
        $link_target = !empty($args['link_target']) ? " target='" . esc_attr($args['link_target']) . "'" : "";
        $link_aria_label = !empty($args['link_aria_label']) ? " aria-label='" . esc_attr($args['link_aria_label']) . "'" : "";
        $featured_image = isset($post['_embedded']['wp:featuredmedia'][0]['source_url']) ? $post['_embedded']['wp:featuredmedia'][0]['source_url'] : '';
        $date_format = $args['format_date'];
		
        if ($args['date_format_type'] === 'human') {
            $date_published = 'Published ' . human_time_diff(strtotime($post['date']), current_time('timestamp')) . ' ago';
        } else {
            $date_published = date($date_format, strtotime($post['date']));
        }
		
        $category = isset($post['_embedded']['wp:term'][0][0]['name']) ? $post['_embedded']['wp:term'][0][0]['name'] : '';

        $output .= '<li class="api-article">';
        $output .= '<article class="news-card">';
        $output .= '<div class="news-card__content-wrapper">';
        $output .= "<$heading class='news-card__title'><a href='" . esc_url($post['link']) . "' class='news-card__link'{$link_target}{$link_aria_label}>" . esc_html($post['title']['rendered']) . "</a></$heading>";
        $output .= '<div class="news-card__meta-wrapper">';
        if ($args['show_date'] == 'yes') {
            $output .= "<p class='news-card__date'><span>" . esc_html($date_published) . "</span></p>";
        }
        if ($args['show_category'] == 'yes') {
            $output .= "<p class='news-card__category'><span>" . esc_html($category) . "</span></p>";
        }
        $output .= '</div>';
        if ($args['show_excerpt'] == 'yes') {
            $output .= "<p class='news-card__excerpt'>" . wp_kses_post($post['excerpt']['rendered']) . "</p>";
        }
        $output .= '</div>';
        if ($args['show_img'] == 'yes') {
        $output .= '<figure class="news-card__img-wrapper">';
        $output .= "<img src='" . esc_url($featured_image) . "' alt='' class='news-card__img'>";
        $output .= '</figure>';
        }
        $output .= '</article>';
        $output .= '</li>';
    }

    $output .= '</ul>';

    return $output;
}


add_shortcode('api_articles', 'api_articles_shortcode');

// retrieve categories

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

// Get tags

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

// Get custom post types

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

// Get taxonomies

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

