# Google News Shortcode Plugin (0.0)

## Description

This is a plugin that allows you to use the shortcode `[google-news]` to create a search box
for google news.  This code will create a javascipt-only input that retrieves results for searches
of at least three characters long asynchronously and loads them into an element below.

## Installation

### Requirements

* PHP 5.3.6 (untested on other versions, but should work all 5.2 to 5.4 versions)
 * PHP `curl` **OR** `fopen` wrappers enabled
* Wordpress 3.3.2 (untested on other versions)
* jQuery 1.7.1

### Instructions

1. Manually move the contents of the `src` directory to the WordPress `plugins/gn-search` directory.
1. Active the plugin
1. Configure the plugin via the Admin panel
1. ????
1. Profit

## API

You can use the bare shortcode `[google-news]` to use default options.  Otherwise, you may set
the following options as attributes of the shortcode:

* `chars` -- required number of characters to initiate search (default: 3)
* `placeholder` -- placeholder text (only in browsers that support the `placeholder` attribute (default: none)
* `id` -- ID of the element to load results into (default: gn-search-container)
 * **WARNING:** If provided, the element that ordinarily has the results is omitted from the response.  You must
   provide your own
* `results` -- Number of results to be displayed (any amount over this will display a "show more" link)
   (default: 0 -- unlimited)
* `limit` -- Total number of allowed results (default: 0 -- unlimited)

**NOTE:** The input has an `id` attribute of `gn-search`.

## License

This code is in the public domain
