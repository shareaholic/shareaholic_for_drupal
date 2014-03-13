<?php
/**
 * Shareaholic Sequential Share Count
 *
 * @package shareaholic
 */

require_once('share_counts.php');

/**
 * A class that implements ShareaholicShareCounts
 * This class will get the share counts by calling
 * the social services sequentially
 *
 * @package shareaholic
 */

class ShareaholicSeqShareCount extends ShareaholicShareCount {

  /**
   * This function should get all the counts for the
   * supported services
   *
   * It should return an associative array with the services as
   * the keys and the counts as the value.
   *
   * Example:
   * array('facebook' => 12, 'google_plus' => 0, 'twitter' => 14, ...);
   *
   * @return Array an associative array of service => counts
   */
  public function get_counts() {
    $services_length = count($this->services);
    $config = $this->get_services_config();
    $response = array();

    for($i = 0; $i < $services_length; $i++) {
      $service = $this->services[$i];

      if(!isset($config[$service])) {
        $response[$service] = 0;
        continue;
      }

      if(isset($config[$service]['prepare'])) {
        $this->$config[$service]['prepare']($this->url, $config);
      }

      $options = array(
        'method' => $config[$service]['method'],
        'timeout' => 2,
        'headers' => isset($config[$service]['headers']) ? $config[$service]['headers'] : array(),
        'body' => isset($config[$service]['body']) ? $config[$service]['body'] : NULL,
      );

      $result = ShareaholicHttp::send(sprintf($config[$service]['url'], $this->url), $options);
      $callback = $config[$service]['callback'];
      $response[$service] = $this->$callback($result);
    }
    return $response;
  }


}