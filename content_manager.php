<?php
/**
 * File for the ShareaholicContentManager class.
 *
 * @package shareaholic
 */

/**
 * An interface to the Shareaholic Content Manager API's
 *
 * @package shareaholic
 */
class ShareaholicContentManager {


  /**
   * Wrapper for the Shareaholic Content Manager Single Domain worker API
   *
   * @param string $domain
   */
  public static function single_domain_worker($domain = NULL) {
    if ($domain == NULL) {
      $domain = $GLOBALS['base_url'];
    }

    if ($domain != NULL) {
      $single_domain_job_url = ShareaholicUtilities::CM_API_URL . '/jobs/single_domain';
      $data = '{"args":["' . $domain . '", {"force": true}]}';
      $options = array(
        'method' => 'POST',
        'data' => $data,
        'headers' => array('Content-Type' => 'application/json'),
      );
      $response = drupal_http_request($single_domain_job_url, $options);
    }
  }

}