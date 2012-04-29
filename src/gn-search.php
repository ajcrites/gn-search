<?php
/**
 * The purpose of this file is to create a plugin for a short code for a Google News search
 * @author Andrew Crites <explosion-pills@aysites.com>
 * @package gn-search
 */
/*
Plugin Name: Google News Search
Description: A shortcode that creates an input that response with an asynchronous search of Google News
Version: 0.0
Author: Andrew Crites
License: Unlicense
*/

require_once dirname(__FILE__) . '/gn-search-ajax.php';

class GnSearchShortcode {
   /**
    * @var WP_Error container for errors in setup
    */
   private $errors;

   /**
    * Initialize all necessary WP settings to work with ajax and the required JavaScript/styles
    */
   public static function init() {
      wp_enqueue_script('my-ajax-request', plugins_url('gn-search/js/gn-search.js'), array('jquery'));
      wp_localize_script('my-ajax-request', 'GnSearch', array('ajaxurl' => admin_url('admin-ajax.php')));

      add_action('wp_ajax_nopriv_gnsearch', array('GnSearchAjax', 'gn_search_ajax'));
      add_action('wp_ajax_gnsearch', array('GnSearchAjax', 'gn_search_ajax'));

      wp_register_style('gn-search.css', plugins_url('gn-search/css/gn-search.css'));
      wp_enqueue_style('gn-search.css');
   }

   public function __construct() {
      $this->errors = new WP_Error;
   }

   public static function gnsearch_shortcode_func($atts) {
      $self = new self;
      return $self->run($atts);
   }

   /**
    * Create the shortcode based on provided attributes
    * @param array WP attributes
    * @return string html necessary for search
    */
   public function run($atts) {
      //I hate to use `extract`, but this seems to be the standard
      extract( shortcode_atts( array(
         'chars' => 3,
         'placeholder' => '',
         'id' => 'gn-search-container',
         'results' => 0,
         'limit' => 0
      ), $atts ) );

      $chars = $this->valid_int($chars, 1, 3);
      $results = $this->valid_int($results, 0);
      $limit = $this->valid_int($limit, 0);

      $html = <<<HTML
         <input type="text" placeholder="$placeholder" id="gn-search" data-chars="$chars" data-container="$id"
            data-limit="$limit" data-results="$results"
         />
HTML;
      if ($id == 'gn-search-container') {
         $html .= <<<HTML
            <section id="gn-search-container"></section>
HTML;
      }
      else if (!$id) {
         $this->errors->add('invalid_id', __(__METHOD__ . ': you must specify an id attribute for the container element'
            . ' or use the default'));
         //The input is useless without an id to refer to, so leave it blank
         return '';
      }
      return $html;
   }

   /**
    * Validate the given input by confirming it is a usable digit and greater than the minimum
    * @var mixed parameter received from short code
    * @var int minimum value of this parameter
    * @var int the default value if the provided value is invalid (max, if none is provided)
    * @return int
    */
   public function valid_int($value, $max, $default = null) {
      if ($default === null) {
         $default = $max;
      }

      if (!is_int($value) && !ctype_digit($value) || $value < $max) {
         $this->errors->add('invalid_results', __(__METHOD__ . ': you must specify 0 or a digit for results to display'));
         return $default;
      }
      return $value;
   }

   public function get_errors() {
      return $this->errors;
   }
}

add_action('init', array('GnSearchShortcode', 'init'));
add_shortcode('google-news', array('GnSearchShortcode', 'gnsearch_shortcode_func'));
?>
