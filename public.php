<?php
/**
 * Holds the ShareaholicPublic class.
 *
 * @package shareaholic
 */

// Load the necessary libraries for Share Count API
module_load_include('php', 'shareaholic', 'lib/social-share-counts/drupal_http');
module_load_include('php', 'shareaholic', 'lib/social-share-counts/seq_share_count');
module_load_include('php', 'shareaholic', 'lib/social-share-counts/curl_multi_share_count');
module_load_include('php', 'shareaholic', 'public_js');

/**
 * This class is all about drawing the stuff in publishers'
 * templates that visitors can see.
 *
 * @package shareaholic
 */
class ShareaholicPublic {

  /**
   * Inserts the script code snippet into the head of the
   * public pages of the site if they have accepted ToS and have apikey
   */
  public static function insert_script_tag() {
    if (!ShareaholicUtilities::is_admin_page() &&
        ShareaholicUtilities::has_tos_and_apikey()) {
        $markup = self::js_snippet();
        $element = array(
          '#type' => 'markup',
          '#markup' => $markup,
        );
      drupal_add_html_head($element, 'shareaholic_script_tag');
    }
  }

  /**
   * The actual text for the JS snippet because drupal doesn't seem to be
   * able to add JS from template like Wordpress does...
   * Using heredocs for now
   *
   * @return string JS block for shareaholic code
   */
  private static function js_snippet() {
    $api_key = ShareaholicUtilities::get_option('api_key');
    $js_url = ShareaholicUtilities::asset_url('pub/shareaholic.js');
    $page_config = ShareaholicPublicJS::get_page_config();
    $js_snippet = <<< DOC
  <script type='text/javascript' data-cfasync='false'>
    //<![CDATA[
      (function() {
        var shr = document.createElement('script');
        shr.setAttribute('data-cfasync', 'false');
        shr.src = '$js_url';
        shr.type = 'text/javascript'; shr.async = 'true';
        shr.onload = shr.onreadystatechange = function() {
          var rs = this.readyState;
          if (rs && rs != 'complete' && rs != 'loaded') return;
          var site_id = '$api_key';
          var page_config = $page_config;
          try { Shareaholic.init(site_id, page_config); } catch (e) {}
        };
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(shr, s);
      })();
    //]]>
  </script>

DOC;
    return $js_snippet;
  }

  /**
   * Insert the disable analytics meta tag
   */
  public static function insert_disable_analytics_meta_tag() {
    if(!ShareaholicUtilities::is_admin_page() &&
        ShareaholicUtilities::has_tos_and_apikey() &&
        ShareaholicUtilities::get_option('disable_analytics') === 'on') {
      $element = array(
        '#type' => 'html_tag',
        '#tag' => 'meta',
        '#attributes' => array(
          'name' => 'shareaholic:analytics',
          'content' => 'disabled'
        ),
      );
      drupal_add_html_head($element, 'shareaholic_disable_analytics');
    }
  }

  /**
   * Inserts the xua-compatible header if the user has accepted
   * ToS and has API key
   */
  public static function set_xua_compatible_header() {
    if(ShareaholicUtilities::has_tos_and_apikey() &&
        !drupal_get_http_header('X-UA-Compatible')) {
      drupal_add_http_header('X-UA-Compatible', 'IE=edge,chrome=1');
    }
  }
  /**
   * Inserts the shareaholic content meta tags on the page
   * On all pages, it will insert the standard content meta tags
   * On full post pages, it will insert page specific content meta tags
   *
   */
  public static function insert_content_meta_tags($node = NULL, $view_mode = NULL, $lang_code = NULL) {
    if($view_mode === 'rss') {
      return;
    }
    $site_name = ShareaholicUtilities::site_name();
    $api_key = ShareaholicUtilities::get_option('api_key');
    $module_version = ShareaholicUtilities::get_version();
    $content_tags = <<<DOC

<!-- Shareaholic Content Tags -->
<meta name='shareaholic:site_name' content='$site_name' />
<meta name='shareaholic:language' content='$lang_code' />
<meta name='shareaholic:site_id' content='$api_key' />
<meta name='shareaholic:drupal_version' content='$module_version' />
DOC;
    if(isset($node) && isset($view_mode) && $view_mode === 'full') {
      $url = $GLOBALS['base_root'] . request_uri();
      $published_time = date('c', $node->created);
      $modified_time = date('c', $node->changed);
      $author = user_load($node->uid);
      $author_name = self::get_user_name($author);
      $tags = implode(', ', self::get_keywords_for($node));
      $image_url = self::get_image_url_for($node);
      $visibility = self::get_visibility($node);

      $content_tags .= "\n<meta name='shareaholic:url' content='$url' />";
      $content_tags .= "\n<meta name='shareaholic:article_published_time' content='$published_time' />";
      $content_tags .= "\n<meta name='shareaholic:article_modified_time' content='$modified_time' />";
      $content_tags .= "\n<meta name='shareaholic:article_author_name' content='$author_name' />";

      if(!empty($tags)) {
        $content_tags .= "\n<meta name='shareaholic:keywords' content='$tags' />";
      }
      if(!empty($image_url)) {
        $content_tags .= "\n<meta name='shareaholic:image' content='$image_url' />";
      }
      if(!empty($visibility)) {
        $content_tags .= "\n<meta name='shareaholic:article_visibility' content='$visibility' />";
      }
    }
    $content_tags .= "\n<!-- Shareaholic Content Tags End -->\n";

    $element = array(
      '#type' => 'markup',
      '#markup' => $content_tags
    );

    drupal_add_html_head($element, 'shareaholic_content_meta_tags');
  }

  /**
   * Get the user's name from an account object
   * If the user has a full name, then that is returned
   * Otherwise it returns the user's username
   *
   * @return String the user name
   */
  public static function get_user_name($account) {
    $full_name = isset($account->field_fullname) ? $account->field_fullname : false;
    $full_name = isset($account->field_full_name) ? $account->field_full_name : $full_name;

    if($full_name && isset($full_name['und']['0']['value'])) {
      $full_name = $full_name['und']['0']['value'];
    } else {
      $first_name = isset($account->field_firstname) ? $account->field_firstname : false;
      $first_name = isset($account->field_first_name) ? $account->field_first_name : $first_name;

      $last_name = isset($account->field_lastname) ? $account->field_lastname : false;
      $last_name = isset($account->field_last_name) ? $account->field_last_name : $last_name;

      if(!empty($first_name) && !empty($last_name) && isset($first_name['und']['0']['value']) && isset($last_name['und']['0']['value'])) {
        $full_name = $first_name['und']['0']['value'] . ' ' . $last_name['und']['0']['value'];
      }
    }
    return (!empty($full_name)) ? $full_name : $account->name;
  }

  /**
   * Get a list of tags for a piece of content
   *
   * @return Array an array of terms or empty array
   */
  public static function get_keywords_for($node) {
    $terms = array();
    $results = db_query('SELECT tid FROM {taxonomy_index} WHERE nid = :nid', array(':nid' => $node->nid));
    foreach ($results as $result) {
      $term = taxonomy_term_load($result->tid);
      if(empty($term) || empty($term->name)) {
        continue;
      }
      array_push($terms, ShareaholicUtilities::clean_string($term->name));
      $vocabulary = taxonomy_vocabulary_load($term->vid);
      if(empty($vocabulary) || empty($vocabulary->name) || preg_match('/tags/i', $vocabulary->name)) {
        continue;
      }
      array_push($terms, ShareaholicUtilities::clean_string($vocabulary->name));
    }
    $terms = array_unique($terms);
    return $terms;
  }

  /**
   * Get image used in a piece of content
   *
   * @return mixed either returns a string or false if no image is found
   */
  public static function get_image_url_for($node) {
    if(isset($node->field_image['und']['0']['uri'])) {
      return file_create_url($node->field_image['und']['0']['uri']);
    }
    if(isset($node->body) && isset($node->body['und']['0']['value'])) {
      return self::post_first_image($node->body['und']['0']['value']);
    }
  }

  /**
   * Copied straight out of the wordpress version,
   * this will grab the first image in a post.
   *
   * @return mixed either returns `false` or a string of the image src
   */
  public static function post_first_image($body) {
    preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"].*>/i', $body, $matches);
    if(isset($matches) && isset($matches[1][0]) ) {
        $first_img = $matches[1][0];
    }
    if(empty($first_img)) { // return false if nothing there, makes life easier
      return false;
    }
    return $first_img;
  }

  /**
   * Inserts the Shareaholic widget/apps on the page
   * By drawing the canvas on a piece of content
   *
   * @param $node The node object representing a piece of content
   * @param $view_mode The view that tells how to show the content
   * @param $lang_code The language code
   */
  public static function insert_widgets($node, $view_mode, $lang_code) {
    if($view_mode === 'rss') {
      return;
    }
    if(isset($node->content)) {
      self::draw_canvases($node, $view_mode);
    }
  }

  /**
   * This static function inserts the shareaholic canvas in a node
   *
   * @param  string $node The node object to insert the canvas into
   */
  public static function draw_canvases(&$node, $view_mode) {
    $settings = ShareaholicUtilities::get_settings();
    $page_type = $node->type;
    $sb_above_weight = -1000;
    $rec_above_weight = -999;
    $sb_below_weight = 1000;
    $rec_below_weight = 1001;
    if($view_mode === 'teaser') {
      $page_type = 'teaser';
    }
    foreach (array('share_buttons', 'recommendations') as $app) {
      if(isset($node->shareaholic_options["shareaholic_hide_{$app}"]) && $node->shareaholic_options["shareaholic_hide_{$app}"]) {
        continue;
      }
      $title = $node->title;
      $summary = isset($node->teaser) ? $node->teaser : '';
      $link = url('node/'. $node->nid, array('absolute' => TRUE));

      if (isset($settings[$app]["{$page_type}_above_content"]) &&
          $settings[$app]["{$page_type}_above_content"] == 'on') {
        $id = $settings['location_name_ids'][$app]["{$page_type}_above_content"];
        $node->content["shareaholic_{$app}_{$page_type}_above_content"] = array(
          '#markup' => self::canvas($id, $app, $title, $link),
          '#weight' => ($app === 'share_buttons') ? $sb_above_weight : $rec_above_weight
        );
      }

      if (isset($settings[$app]["{$page_type}_below_content"]) &&
          $settings[$app]["{$page_type}_below_content"] == 'on') {
        $id = $settings['location_name_ids'][$app]["{$page_type}_below_content"];
        $node->content["shareaholic_{$app}_{$page_type}_below_content"] = array(
          '#markup' => self::canvas($id, $app, $title, $link),
          '#weight' => ($app === 'share_buttons') ? $sb_below_weight : $rec_below_weight
        );
      }
    }
  }

  /**
   * Draws an individual canvas given a specific location
   * id and app
   *
   * @param string $id  the location id for configuration
   * @param string $app the type of app
   * @param string $title the title of URL
   * @param string $link url
   * @param string $summary summary text for URL
   */
  public static function canvas($id, $app, $title = NULL, $link = NULL, $summary = NULL) {

    $title = trim(htmlspecialchars($title, ENT_QUOTES));
    $link = trim($link);
    $summary = trim(htmlspecialchars(strip_tags($summary), ENT_QUOTES));

    $canvas = "<div class='shareaholic-canvas'
      data-app-id='$id'
      data-app='$app'
      data-title='$title'
      data-link='$link'
      data-summary='$summary'></div>";

    return trim(preg_replace('/\s+/', ' ', $canvas));
  }


  /**
   * Determines the visibility of a piece of content
   * and returns that value
   *
   * Possible values are: 'draft', 'private', and NULL
   *
   * @param Object $node The content to determine its visibility
   * @return String a string indicating its visibility
   */
  public static function get_visibility($node) {
    $visibility = NULL;
    // Check if it is a draft
    if(isset($node->status) && $node->status == 0) {
      $visibility = 'draft';
    }
    // Check if it should be excluded from recommendations
    if(isset($node->shareaholic_options) && $node->shareaholic_options['shareaholic_exclude_from_recommendations']) {
      $visibility = 'private';
    }

    return $visibility;
  }


  /**
   * Function to handle the share count API requests
   *
   */
  public static function share_counts_api() {
    // sometimes the drupal http request function throws errors so setting handler
    set_error_handler(array('ShareaholicPublic', 'custom_error_handler'));

    $cache_key = 'shr_api_res-' . md5( $_SERVER['QUERY_STRING'] );
    $result = ShareaholicCache::get($cache_key);

    if (!$result) {
      $url = isset($_GET['url']) ? $_GET['url'] : NULL;
      $services = isset($_GET['services']) ? $_GET['services'] : NULL;
      $result = array();

      if(is_array($services) && count($services) > 0 && !empty($url)) {
        if(self::has_curl()) {
          $shares = new ShareaholicCurlMultiShareCount($url, $services);
        } else {
          $shares = new ShareaholicSeqShareCount($url, $services);
        }
        $result = $shares->get_counts();

        if (isset($result['data'])) {
          ShareaholicCache::set($cache_key, $result, SHARE_COUNTS_CHECK_CACHE_LENGTH);
        }
      }
    }

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
  }


  /**
   * Custom error handler
   *
   * @param integer $errno The error level as an integer
   * @param string $errstr The error string
   * @param string $errfile The file where the error occurred
   * @param string $errline The line number where the error occurred
   */
  public static function custom_error_handler($errno, $errstr, $errfile, $errline) {
    ShareaholicUtilities::log($errstr . ' ' . $errfile . ' ' . $errline);
  }

  /**
   * Checks to see if curl is installed
   *
   * @return bool true or false that curl is installed
   */
  public static function has_curl() {
    return function_exists('curl_version') && function_exists('curl_multi_init') && function_exists('curl_multi_add_handle') && function_exists('curl_multi_exec');
  }

  /**
   * Insert the Open Graph Tags
   */
  public static function insert_og_tags($node = false, $view_mode = '') {
    $markup = '';
    $disable_og_tags_check = ShareaholicUtilities::get_option('disable_og_tags');
    if ($disable_og_tags_check && $disable_og_tags_check == 'on') {
      return;
    }
    if ($view_mode != 'full') {
      return;
    }
    if ($node && (!isset($node->shareaholic_options["shareaholic_exclude_og_tags"]) || !$node->shareaholic_options["shareaholic_exclude_og_tags"]) ) {
      $image_url = self::get_image_url_for($node);
      if (!empty($image_url)) {
        $markup .= "\n<!-- Shareaholic Open Graph Tags -->\n";
        $markup .= "<meta property='og:image' content='" . $image_url . "' />";
        $markup .= "\n<!-- Shareaholic Open Graph Tags End -->\n";
      }
    }
    $element = array(
      '#type' => 'markup',
      '#markup' => $markup,
    );
    drupal_add_html_head($element, 'shareaholic_og_tags');
  }

}
