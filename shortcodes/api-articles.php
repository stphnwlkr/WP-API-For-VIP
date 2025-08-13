<?php

// Ensure the plugin is only executed within WordPress.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function api_articles_shortcode( $atts ) {
    $args = shortcode_atts( array(
        'endpoint'         => '',
        'count'            => 6,
        'offset'           => 0,

        // NEW: preferred controls
        'wp_date'          => 'h',         // 'h' (human) or 'd' (normal/wp format)
        'date_format'      => '',          // used when wp_date='d'; falls back to get_option('date_format')

        // BACK-COMPAT (will be mapped to the new ones if present)
        'format_date'      => 'F d, Y',    // legacy
        'date_format_type' => 'human',     // legacy ('human' or anything else)

        'post_type'        => 'posts',
        'show_excerpt'     => 'yes',
        'show_date'        => 'yes',
        'show_category'    => 'yes',
        'category'         => '',
        'cat_exclude'      => '',
        'tag'              => '',
        'heading_level'    => 'h2',
        'show_img'         => 'yes',
        'article_class'    => '',
        'post_slug'        => '',
        'link_target'      => '',  // e.g. '_blank'
        'link_aria_label'  => '',
        'order_by'         => 'date',
        'order_direction'  => 'desc',
        'taxonomy_name'    => '',
        'taxonomy_value'   => '',
        'clear_cache'      => 'no',
        'cache_duration'   => '15',
        'timeout_message'  => 'Refresh your browser for the latest content.',
    ), $atts );

    // Sanitize/normalize
    $args = array_map( 'sanitize_text_field', $args );
    $args['count']         = absint( $args['count'] );
    $args['offset']        = absint( $args['offset'] );
    $args['article_class'] = sanitize_html_class( $args['article_class'] );

    // ---- Backward compatibility mapping ----
    // If the new wp_date wasn't explicitly set (or left default),
    // and legacy controls were passed, map them to the new API.
    if ( isset( $atts['date_format_type'] ) && ! isset( $atts['wp_date'] ) ) {
        // 'human' => 'h', anything else => 'd'
        $args['wp_date'] = ( strtolower( $args['date_format_type'] ) === 'human' ) ? 'h' : 'd';
    }
    if ( isset( $atts['format_date'] ) && ! isset( $atts['date_format'] ) ) {
        $args['date_format'] = $args['format_date'];
    }

    // Normalize wp_date to 'h' or 'd'
    $args['wp_date'] = ( $args['wp_date'] === 'd' ) ? 'd' : 'h';

    if ( empty( $args['endpoint'] ) ) {
        return 'Error: Please specify the API endpoint.';
    }

    // Determine cache duration in seconds
    switch ( $args['cache_duration'] ) {
        case '0':
            $cache_duration = 0;
            break;
        case '30':
            $cache_duration = 30 * MINUTE_IN_SECONDS;
            break;
        case '60':
            $cache_duration = HOUR_IN_SECONDS;
            break;
        case '120':
            $cache_duration = 2 * HOUR_IN_SECONDS;
            break;
        case '15':
        default:
            $cache_duration = 15 * MINUTE_IN_SECONDS;
            break;
    }

    // Generate a unique cache key based on the arguments
    $cache_key = 'api_articles_' . md5( wp_json_encode( $args ) );

    // Clear cache if requested
    if ( $args['clear_cache'] === 'yes' ) {
        delete_transient( $cache_key );
    }

    // Check if a cached response exists
    $cached_response = get_transient( $cache_key );
    if ( $cached_response !== false && $cache_duration !== 0 ) {
        return $cached_response;
    }

    $category_query = '';
    if ( ! empty( $args['category'] ) ) {
        $category_query = "&categories={$args['category']}";
    }

    $category_exclude_query = '';
    if ( ! empty( $args['cat_exclude'] ) ) {
        $category_exclude_query = "&categories_exclude={$args['cat_exclude']}";
    }

    $tag_query = '';
    if ( ! empty( $args['tag'] ) ) {
        $tag_query = "&tags={$args['tag']}";
    }

    $order_query = "&orderby={$args['order_by']}&order={$args['order_direction']}";

    $taxonomy_query = '';
    if ( ! empty( $args['taxonomy_name'] ) && ! empty( $args['taxonomy_value'] ) ) {
        $taxonomy_query = "&{$args['taxonomy_name']}=" . rawurlencode( $args['taxonomy_value'] );
    }

    if ( ! empty( $args['post_slug'] ) ) {
        $response = vip_safe_wp_remote_get(
            "{$args['endpoint']}/wp-json/wp/v2/{$args['post_type']}?_embed&slug=" . rawurlencode( $args['post_slug'] ),
            false,
            3,
            3
        );
    } else {
        $response = vip_safe_wp_remote_get(
            "{$args['endpoint']}/wp-json/wp/v2/{$args['post_type']}?_embed&per_page={$args['count']}&offset={$args['offset']}{$category_query}{$category_exclude_query}{$tag_query}{$order_query}{$taxonomy_query}",
            false,
            3,
            3
        );
    }

    if ( is_wp_error( $response ) ) {
        error_log( $response->get_error_message() );
        return 'Error: ' . esc_html( $response->get_error_message() );
    }

    // Check for cURL errors
    $http_code = wp_remote_retrieve_response_code( $response );
    if ( (int) $http_code === 0 ) {
        return esc_html( $args['timeout_message'] );
    }

    if ( (int) $http_code !== 200 ) {
        return 'There is a problem and we are now working on it. HTTP Response Code: ' . intval( $http_code );
    }

    $posts = wp_remote_retrieve_body( $response );
    $posts = json_decode( $posts, true );

    if ( empty( $posts ) ) {
        return 'No articles are available.';
    }

    // Generate the appropriate HTML tag for the heading
    $heading = preg_match( '/^h[1-6]$/i', $args['heading_level'] ) ? $args['heading_level'] : 'h2';

    $output = '<ul class="api-articles ' . esc_attr( $args['article_class'] ) . '">';

    foreach ( $posts as $post ) {
        $link_target     = ! empty( $args['link_target'] ) ? " target='" . esc_attr( $args['link_target'] ) . "'" : '';
        $link_aria_label = ! empty( $args['link_aria_label'] ) ? " aria-label='" . esc_attr( $args['link_aria_label'] ) . "'" : '';

        $featured_image  = isset( $post['_embedded']['wp:featuredmedia'][0]['source_url'] )
            ? $post['_embedded']['wp:featuredmedia'][0]['source_url']
            : '';

        // ---- Date handling ----
        // WP REST returns 'date' (site's timezone) and 'date_gmt'. We'll use 'date' for display,
        // and date_i18n() for localized output in 'd' mode.
        $date_display   = '';
        $post_timestamp = isset( $post['date'] ) ? strtotime( $post['date'] ) : false;

        if ( $post_timestamp && $args['show_date'] === 'yes' ) {
            if ( $args['wp_date'] === 'h' ) {
                // Human-readable ("Published X ago")
                $diff          = human_time_diff( $post_timestamp, current_time( 'timestamp' ) );
                $date_display  = sprintf( 'Published %s ago', $diff );
            } else {
                // Normal WP format
                $format        = trim( $args['date_format'] ) !== '' ? $args['date_format'] : get_option( 'date_format' );
                // sanitize the format lightly (keep typical date tokens and separators)
                $format        = preg_replace( '/[^A-Za-z :\/\-\.,_]/', '', $format );
                $date_display  = date_i18n( $format, $post_timestamp );
            }
        }

        $category = isset( $post['_embedded']['wp:term'][0][0]['name'] ) ? $post['_embedded']['wp:term'][0][0]['name'] : '';

        $output .= '<li class="api-article">';
        $output .= '<article class="news-card focus-parent" style="position:relative">';
        $output .= '<div class="news-card__content-wrapper">';
        $output .= "<{$heading} class='news-card__title clickable-parent'><a href='" . esc_url( $post['link'] ) . "' class='news-card__link'{$link_target}{$link_aria_label}>" . esc_html( $post['title']['rendered'] ) . "</a></{$heading}>";

        $output .= '<div class="news-card__meta-wrapper">';
        if ( $args['show_date'] === 'yes' && $date_display !== '' ) {
            $output .= "<p class='news-card__date'><span>" . esc_html( $date_display ) . "</span></p>";
        }
        if ( $args['show_category'] === 'yes' && $category !== '' ) {
            $output .= "<p class='news-card__category'><span>" . esc_html( $category ) . "</span></p>";
        }
        $output .= '</div>';

        if ( $args['show_excerpt'] === 'yes' && isset( $post['excerpt']['rendered'] ) ) {
            $output .= "<p class='news-card__excerpt'>" . wp_kses_post( $post['excerpt']['rendered'] ) . "</p>";
        }

        $output .= '</div>';

        if ( $args['show_img'] === 'yes' && $featured_image ) {
            $alt_text = isset( $post['_embedded']['wp:featuredmedia'][0]['alt_text'] ) && ! empty( $post['_embedded']['wp:featuredmedia'][0]['alt_text'] )
                ? esc_attr( $post['_embedded']['wp:featuredmedia'][0]['alt_text'] )
                : 'Read ' . esc_attr( wp_strip_all_tags( $post['title']['rendered'] ) );

            $output .= '<figure class="news-card__img-wrapper">';
            $output .= "<img src='" . esc_url( $featured_image ) . "' alt='" . $alt_text . "' class='news-card__img'>";
            $output .= '</figure>';
        }

        $output .= '</article>';
        $output .= '</li>';
    }

    $output .= '</ul>';

    // Cache the response for the specified duration
    if ( $cache_duration !== 0 ) {
        set_transient( $cache_key, $output, $cache_duration );
    }

    return $output;
}

add_shortcode( 'api_articles', 'api_articles_shortcode' );