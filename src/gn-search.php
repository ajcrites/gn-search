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
      //TODO use plugin_content_dir(__FILE__) after I stop my weird testing method for this
      wp_enqueue_script('my-ajax-request', '/wp-content/plugins/gn-search/js/gn-search.js', array('jquery'));
      wp_localize_script('my-ajax-request', 'GnSearch', array('ajaxurl' => admin_url('admin-ajax.php')));

      add_action('wp_ajax_nopriv_gnsearch', array('GnSearchAjax', 'gn_search_ajax'));
      add_action('wp_ajax_gnsearch', array('GnSearchAjax', 'gn_search_ajax'));
   }

   public function __construct() {
      $this->errors = new WP_Error;
   }

   public function gnsearch_shortcode_func($atts) {
      //I hate to use `extract`, but this seems to be the standard
      extract( shortcode_atts( array(
         'chars' => 3,
         'placeholder' => '',
         'id' => 'gn-search-container',
      ), $atts ) );

      if (!is_int($chars) || $chars < 1) {
         $this->errors->add('invalid_chars', __(__METHOD__ . ': you must specify at least 1 character to respond to'));
      }

      $html = <<<HTML
         <input type="text" placeholder="$placeholder" id="gn-search" data-chars="$chars" data-container="$id" />
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

add_shortcode('gn-search', array('GnSearchShortcode', 'gnsearch_shortcode_func'));
add_action('init', array('GnSearchShortcode', 'init'));
?>
