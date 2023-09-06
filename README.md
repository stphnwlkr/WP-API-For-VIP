<img width="1520" alt=" screenshot from the Department of Veterans Affairs usage of this plugin" src="https://github.com/stphnwlkr/WP-API-For-VIP/assets/1405187/1b0e0929-1092-452f-b94b-83eb428a402c">

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
 ## Parameter

* endpoint: Set the source (required)
* count: Set the number of articles - default is 6
* offset: Set an offset (start at article 5) - default is 0
* heading_level: Set the heading level (h2-h4) - default is h2
* category: Choose a category (requires cat ID) - default is none
* post_type: Choose a post type (requires post type slug) - default is post
* Whether to show image, category, excerpt, or date - all are visible by default*   
    - show_category
    - show_excerpt
    - show_date
    - show_img
* article_class: Add layout modifier - default is none


## Shortcode

- [api_articles endpoint="https://example.com" count="5" show_excerpt="yes" show_date="yes" category="2" heading_level="h2"] 
- echo do_shortcode('[api_articles endpoint="https://example.com" count="5" show_excerpt="yes" show_date="yes"]');

# Disclaimer
This code is provided as is. Every attempt has been made to provide good code, but there is no expressed warranty or guarantee. Test the code prior to using it on a production site.
