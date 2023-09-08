![vip-screenshot](https://github.com/stphnwlkr/WP-API-For-VIP/assets/1405187/d9ebdb41-42a0-4f1f-8247-36dc628e3dfa)


# API Element
 A shortcode that pulls articles using the WP API. Sample CSS is provided. The CSS needs to be reworked to combine both layouts in a single API request.

## Version 1.3 
Restructured the html layout to prvide the correct DOM order for accessibility. Add a dynamic modifier so that the base layout is the same but can easily be changed. Also added the ability to not show an image in the event you want just a list of links.

## Version 1.4
Added human readable time difference option (e.g., Published 3 days ago)

## Version 1.5
Add option to receive a single post by slug

## Version 1.6
* Added Tags to the list of query options
* Added an optional link_target option
* Added custom link_aria_label
## Version 1.7
* Added cat_exclude to exclude categories
* Added second shortcode to output a list of category names and IDs
## Version 1.8
* Retrieve custom post types and slugs
* Retrieve custom taxonomy names and slugs (this is of little benefit yet until functionality is addded to filter by custom taxonomy values)
## Version 1.9
* add order by and order direction parameters
* add custom taxonomy and custom taxonomy value parameter
* add shortcode to retrieve custom taxonomy terms based on custom taxonomy slug
 ## Parameter

* endpoint: Set the source (required)
* count: Set the number of articles - default is 6
* offset: Set an offset (start at article 5) - default is 0
* heading_level: Set the heading level (h2-h4) - default is h2
* category: Choose a category (requires cat ID) - default is none
* cat_exclude: exclude categories from the request
* tag: list of tags to include
* post_type: Choose a post type (requires post type slug) - default is post
* Whether to show image, category, excerpt, or date - all are visible by default*   
    - show_category
    - show_excerpt
    - show_date
    - show_img
* article_class: Add layout modifier - default is none
* link_target: primarily to add _blank
* link_aria_label: add a cusom aria-label especially if the link opens a new window


## Shortcode

* [api_articles endpoint="https://example.com" count="5" show_excerpt="yes" show_date="yes" category="2" heading_level="h2"] 
* echo do_shortcode('[api_articles endpoint="https://example.com" count="5" show_excerpt="yes" show_date="yes"]');

### Category List
* [fetch_categories endpoint="https://your-wordpress-site.com"]
# Disclaimer
This code is provided as is. Every attempt has been made to provide good code, but there is no expressed warranty or guarantee. Test the code prior to using it on a production site.
