<?php
/**
 * Holds the ShareaholicPublic class.
 *
 * @package shareaholic
 */

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
        !ShareaholicUtilities::is_edit_page() &&
        ShareaholicUtilities::has_tos_and_apikey()) {
        $markup = self::js_snippet();
        $element = array(
          '#type' => 'markup',
          '#markup' => $markup,
          '#weight' => 20000
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
          try { Shareaholic.init(site_id); } catch (e) {}
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
  public function insert_disable_analytics_meta_tag() {
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
        '#weight' => 10000,
      );
      drupal_add_html_head($element, 'shareaholic_disable_analytics');
    }
  }

  /**
   * Inserts the xua-compatible header if the user has accepted
   * ToS and has API key
   */
  public function set_xua_compatible_header() {
    if(ShareaholicUtilities::has_tos_and_apikey() &&
        !drupal_get_http_header('X-UA-Compatible')) {
      drupal_add_http_header('X-UA-Compatible', 'IE=edge,chrome=1');
    }
  }

  public function insert_content_meta_tags($node = NULL, $view_mode = NULL, $lang_code = NULL) {
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
      $tags = implode(', ', self::get_tags_for($node));
      $image_url = self::get_image_url_for($node);

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
  public static function get_tags_for($node) {
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
    preg_match_all('/<img[\s]+src=[\'"]([^\'"]+)[\'"].*>/i', $body, $matches);
    if(isset($matches) && isset($matches[1][0]) ) {
        $first_img = $matches[1][0];
    }
    if(empty($first_img)) { // return false if nothing there, makes life easier
      return false;
    }
    return $first_img;
  }
}
