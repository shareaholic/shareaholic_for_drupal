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

  public function insert_content_meta_tags($node, $view_mode, $lang_code) {
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
    if($view_mode === 'full') {
      $url = $GLOBALS['base_root'] . request_uri();
      $published_time = date(DATE_ATOM, $node->created);
      $modified_time = date(DATE_ATOM, $node->changed);
      $author = user_load($node->uid);
      $author_name = ShareaholicUtilities::get_user_name($author);
      $tags = implode(', ', ShareaholicUtilities::get_tags_for($node));

      $content_tags .= "\n<meta name='shareaholic:url' content='$url' />";
      $content_tags .= "\n<meta name='shareaholic:article_published_time' content='$published_time' />";
      $content_tags .= "\n<meta name='shareaholic:article_modified_time' content='$modified_time' />";
      $content_tags .= "\n<meta name='shareaholic:article_author_name' content='$author_name' />";

      if(!empty($tags)) {
        $content_tags .= "\n<meta name='shareaholic:keywords' content='$tags' />";
      }
    }
    $content_tags .= "\n<!-- Shareaholic Content Tags End -->\n";

    $element = array(
      '#type' => 'markup',
      '#markup' => $content_tags
    );

    drupal_add_html_head($element, 'shareaholic_content_meta_tags');
  }
}
