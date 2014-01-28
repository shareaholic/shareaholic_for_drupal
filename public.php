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
   * Inserts the script code snippet into the head of the page
   */
  public static function insert_script_tag($version_param) {
    if (ShareaholicUtilities::has_tos_and_apikey()) {
        drupal_add_js(self::js_snippet($version_param),
          array('type' => 'inline', 'scope' => 'header'));
    }
  }

  /**
   * The actual text for the JS snippet because drupal doesn't seem to be
   * able to add JS from template like Wordpress does...
   * Using heredocs to for now
   *
   * @return string JS block for shareaholic code
   */
  private static function js_snippet($version_param) {
    $api_key = ShareaholicUtilities::get_option('api_key');
    $js_url = ShareaholicUtilities::asset_url('pub/shareaholic.js') . '?ver=' . $version_param;
    $js_snippet = <<< DOC
      //<![CDATA[
        (function() {
          var shr = document.createElement('script');
          shr.setAttribute('data-cfasync', 'false');
          shr.src = '$js_url';
          shr.type = 'text/javascript'; shr.async = 'true';
          shr.onload = shr.onreadystatechange = function() {
            var rs = this.readyState;
            if (rs && rs != 'complete' && rs != 'loaded') return;
            var apikey = '$api_key';
            try { Shareaholic.init(apikey); } catch (e) {}
          };
          var s = document.getElementsByTagName('script')[0];
          s.parentNode.insertBefore(shr, s);
        })();
      //]]>
DOC;
    return $js_snippet;
  }

  /**
   * Insert the disable analytics meta tag
   */
  public function insert_disable_analytics_meta_tag(&$head_elements) {
    if(ShareaholicUtilities::has_tos_and_apikey() &&
       ShareaholicUtilities::get_option('disable_analytics') === 'on') {
      $head_elements['shareaholic_disable_analytics'] = array(
        '#type' => 'html_tag',
        '#tag' => 'meta',
        '#attributes' => array(
          'name' => 'shareaholic:analytics',
          'content' => 'disabled'
        ),
        '#weight' => 10000,
      );
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

}
