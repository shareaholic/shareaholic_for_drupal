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
  public static function insert_script_tag(&$vars) {
    if (!ShareaholicUtilities::is_admin_page() &&
        ShareaholicUtilities::has_tos_and_apikey()) {
        $markup = self::js_snippet();
        $element = array(
          '#type' => 'markup',
          '#markup' => $markup,
          '#weight' => 20000
        );
        $vars['scripts'] .= drupal_render($element);
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
    if(ShareaholicUtilities::has_tos_and_apikey() &&
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
  public function insert_xua_compatible_header() {
    if(ShareaholicUtilities::has_tos_and_apikey() &&
        !drupal_get_http_header('X-UA-Compatible')) {
      drupal_add_http_header('X-UA-Compatible', 'IE=edge,chrome=1');
    }
  }

  /**
   * Insert into the head tag of the public pages
   */
  public function insert_meta_tags() {
    if(!ShareaholicUtilities::is_admin_page()) {
      ShareaholicPublic::insert_disable_analytics_meta_tag();
    }
  }

}
