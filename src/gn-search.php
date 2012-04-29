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

   private $errors;

   public static function init() {
      wp_enqueue_script('my-ajax-request', plugins_url('gn-search/js/gn-search.js'), array('jquery'));
      wp_localize_script('my-ajax-request', 'GnSearch', array('ajaxurl' => admin_url('admin-ajax.php')));

      add_action('wp_ajax_nopriv_gnsearch', array('GnSearchAjax', 'gn_search_ajax'));
      add_action('wp_ajax_gnsearch', array('GnSearchAjax', 'gn_search_ajax'));
   }

   public function __construct() {
      $this->errors = new WP_Error;
   }

   public function gnsearch_shortcode_func($atts) {
      $self = new self;
      return $self->run($atts);
   }

   public function run($atts) {
      //I hate to use `extract`, but this seems to be the standard
      extract( shortcode_atts( array(
         'chars' => 3,
         'placeholder' => '',
         'id' => 'gn-search-container',
         'results' => 0,
         'limit' => 0
      ), $atts ) );

      if (!is_int($chars) && !ctype_digit($chars) || $chars < 1) {
         $this->errors->add('invalid_chars', __(__METHOD__ . ': you must specify at least 1 character to respond to'));
         $chars = 3;
      }

      if (!is_int($results) && !ctype_digit($results) || $results < 0) {
         $this->errors->add('invalid_results', __(__METHOD__ . ': you must specify 0 or a digit for results to display'));
         $results = 0;
      }

      if (!is_int($limit) && !ctype_digit($limit) || $limit < 0) {
         $this->errors->add('invalid_limit', __(__METHOD__ . ': you must specify 0 or a digit for limit on results'));
         $limit = 0;
      }

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

   public function get_errors() {
      return $this->errors;
   }
}

add_shortcode('google-news', array('GnSearchShortcode', 'gnsearch_shortcode_func'));
add_action('init', array('GnSearchShortcode', 'init'));
wp_register_style('gn-search.css', plugins_url('gn-search/css/gn-search.css'));
wp_enqueue_style('gn-search.css');
?>
