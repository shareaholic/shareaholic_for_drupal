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
  public static function insert_script_tag() {
    if (ShareaholicUtilities::has_accepted_terms_of_service() &&
        ShareaholicUtilities::get_or_create_api_key()) {
        drupal_add_js(self::js_snippet(),
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
  private static function js_snippet() {
    $api_key = ShareaholicUtilities::get_option('api_key');
    $js_url = ShareaholicUtilities::asset_url('pub/shareaholic.js');
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

}
