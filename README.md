![image](https://awb4wp.com/wp-content/uploads/2023/09/grid-post-layout-scaled.jpg)

# API Element

A shortcode that pulls articles using the WP REST API.  
Sample CSS is provided — the CSS should be reworked to combine both layouts in a single API request.

---

## Version History

### Version 1.3
- Restructured the HTML layout to provide the correct DOM order for accessibility.
- Added a dynamic modifier so the base layout is the same but can easily be changed.
- Added the ability to hide the image if you want just a list of links.

### Version 1.4
- Added human-readable time difference option (e.g., “Published 3 days ago”).

### Version 1.5
- Added option to retrieve a single post by slug.

### Version 1.6
- Added Tags to the list of query options.
- Added an optional `link_target` attribute.
- Added custom `link_aria_label` attribute.

### Version 1.7
- Added `cat_exclude` to exclude categories.
- Added second shortcode to output a list of category names and IDs.

#### Version 1.7.1
- Changed `wp_remote_get` to `wp_safe_remote_get` for improved security.

### Version 1.8
- Retrieve custom post types and slugs.
- Retrieve custom taxonomy names and slugs.

### Version 1.9
- Added `order_by` and `order_direction` parameters.
- Added custom taxonomy name and value parameters.
- Added shortcode to retrieve custom taxonomy terms based on custom taxonomy slug.

### Version 2.0
- Restructured the code.

### Version 2.1
- Added error message for cURL timeout issue.

### Version 2.2
- Added caching.

### Version 2.3
- Added caching and error message parameters.

### Version 2.4
- Increased timeout to 3 seconds for `vip_safe_wp_remote_get` (default was 1 second).  
  See [WP VIP docs](https://docs.wpvip.com/databases/optimize-queries/retrieving-remote-data/).
- Added `alt` text to images.

### Version 2.5
- **New:** Added `wp_date` and `date_format` parameters for date display control.
  - `wp_date="h"` → human-readable time difference (default).
  - `wp_date="d"` → normal WP-formatted date. If `date_format=""` is set, uses your custom format; otherwise falls back to the site’s Date Format in **Settings → General**.
- Maintains backward compatibility with legacy `date_format_type` and `format_date` attributes.

---

## Parameters

| Parameter | Description | Default |
|-----------|-------------|---------|
| `endpoint` | **Required.** Set the source (full site URL). | — |
| `count` | Number of articles to retrieve. | `6` |
| `offset` | Offset results (e.g., start at article 5). | `0` |
| `heading_level` | Heading level for titles (`h2`–`h4`). | `h2` |
| `category` | Category ID(s) to include. | — |
| `cat_exclude` | Category ID(s) to exclude. | — |
| `tag` | Tag ID(s) to include. | — |
| `post_type` | Post type slug. | `post` |
| `show_category` | Show category name. | `yes` |
| `show_excerpt` | Show excerpt. | `yes` |
| `show_date` | Show date. | `yes` |
| `show_img` | Show featured image. | `yes` |
| `article_class` | CSS class modifier for layout. | — |
| `link_target` | Link target (`_blank`, etc.). | — |
| `link_aria_label` | Custom ARIA label for link. | — |
| `wp_date` | **New in 2.5.** Date display type: `h` (human-readable) or `d` (normal WP date). | `h` |
| `date_format` | **New in 2.5.** Custom date format string when `wp_date="d"`. If empty, uses WP’s Date Format setting. | — |
| `order_by` | Field to order by (`date`, `title`, etc.). | `date` |
| `order_direction` | Order direction (`asc` or `desc`). | `desc` |
| `taxonomy_name` | Custom taxonomy slug. | — |
| `taxonomy_value` | Custom taxonomy term ID(s) or slug(s). | — |
| `cache_duration` | Cache lifespan in minutes: `0` (no cache), `15`, `30`, `60`, or `120`. | `15` |
| `timeout_message` | Message to show if request times out. | `"Refresh your browser for the latest content."` |

---

## Shortcode Examples

* [api_articles endpoint="https://example.com" count="5" show_excerpt="yes" show_date="yes" category="2" heading_level="h2"] 
* as of 2.3[api_articles endpoint="https://example.com" cache_duration="30" timeout_message="The content is currently unavailable. Please try again later."]
* echo do_shortcode('[api_articles endpoint="https://example.com" count="5" show_excerpt="yes" show_date="yes"]');

### Category List
* [fetch_categories endpoint="https://your-wordpress-site.com"]
# Note
If you are on WordPress VIP, they have some modified functions, and you will want to refer to https://github.com/stphnwlkr/WP-API-For-VIP.


# Disclaimer
This code is provided as is. Every attempt has been made to provide good code, but there is no expressed warranty or guarantee. Test the code prior to using it on a production site.