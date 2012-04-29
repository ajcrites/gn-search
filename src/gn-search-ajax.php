<?php
/**
 * The purpose of this file is to an asynchronous search on Google News for the provided term
 * and parse the results into usable JSON
 * @author Andrew Crites <explosion-pills@aysites.com>
 * @package gn-search
 */

class GnSearchAjax {
   private $errors;

   public function __construct() {
      $this->errors = new WP_Error;
   }

   /**
    * Emit the results of the search attempt as json
    * When successful, this responds with a status of 'success' and a 'response'
    * with the results of the search.
    * If it fails on this end, it response with an error message
    */
   public static function gn_search_ajax() {
      $gns = new self;
      $gns->run();
   }

   public function run() {
      if (!isset($_REQUEST['term']) || !$_REQUEST['term']) {
         $this->errors->add('no_term', __METHOD__ . ': no search term was provided');
         $this->error('Please provide a search term');
      }

      $xml = $this->retrieve('http://news.google.com/news?q=' . urlencode($_REQUEST['term']) . '&output=rss');

      $this->parse_response($xml);
   }

   /**
    * Retrieve feed data as xml from source
    */
   public function retrieve($url) {
      if (ini_get('allow_url_fopen')) {
         $data = file_get_contents($url);
      }
      else if (function_exists('curl_init')) {
         $ch = curl_init($url);
         curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
         ));
         $data = curl_exec($ch);
         $httpst = curl_getinfo($ch, CURLINFO_HTTP_CODE);
         //If a successful response was not returned, trigger the correct error below
         if (!in_array($httpst, array(200, 302, 304))) {
            $data = null;
         }
      }
      else {
         $this->errors->add('no_method', __METHOD__ . ': no available method for download on server side');
         $this->error('This site cannot get data from Google right now');
      }

      if (!$data) {
         $this->errors->add('no_term', __METHOD__ . ': no response from Google News');
         $this->error('No results available for your search');
      }
      return $data;
   }

   /**#@+
    * Emit the response and exit
    */
   private function success($response) {
      header('Content-Type: application/json');
      echo json_encode(array(
         'status' => 'success',
         'response' => $response
      ));
      exit;
   }
   private function error($msg) {
      header('Content-Type: application/json');
      echo json_encode(array(
         'status' => 'error',
         'msg' => $msg
      ));
      exit;
   }
   /**#@-*/

   /**
    * Parse the xml to search for news terms and emit as JSON when successful
    * If no items or found or the xml is otherwise invalid, emit an error
    * @param string search result xml
    */
   public function parse_response($xml) {

      //SimpleXML throws the very helpful "Exception" class when it fails
      try {
         $dom = new SimpleXMLElement($xml);
         if (!$dom->channel) {
            $this->errors->add('xml_parse_error', __METHOD__ . ': no "channel" element found when parsing successful response');
            $this->error('There were no results for your search');
         }
         if (!$dom->channel->item) {
            $this->error('There is no news about ' . $_REQUEST['term'] . ' right now');
         }

         $limit = 0;
         if (isset($_REQUEST['limit']) && ctype_digit($_REQUEST['limit'])) {
            $limit = $_REQUEST['limit'];
         }

         $results = array();
         foreach ($dom->channel->item as $elem) {
            $results[] = array(
               'title' => "$elem->title"
               , 'url' => "$elem->link"
            );
            if ($limit == 1) {
               break;
            }
            $limit--;
         }
         $this->success($results);
      }
      catch (Exception $e) {
         $this->errors->add('xml_parse_error', __METHOD__ . ': unable to parse XML with SimpleXML -- '
            . $e->getMessage());
         $this->error('Sorry! We were unable to get results for your search');
      }
   }
}
?>
