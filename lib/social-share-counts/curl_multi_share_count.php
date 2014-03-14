<?php
/**
 * Shareaholic Multi Share Count
 *
 * @package shareaholic
 */

require_once('share_count.php');

/**
 * A class that implements ShareaholicShareCounts
 * This class will get the share counts by calling
 * the social services via curl_multi
 *
 * @package shareaholic
 */

class ShareaholicCurlMultiShareCount extends ShareaholicShareCount {

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

    // array of curl handles
    $curl_handles = array();

    // multi handle
    $multi_handle = curl_multi_init();

    for($i = 0; $i < $services_length; $i++) {
      $service = $this->services[$i];

      if(!isset($config[$service])) {
        continue;
      }

      if(isset($config[$service]['prepare'])) {
        $this->$config[$service]['prepare']($this->url, $config);
      }

      $endpoint = sprintf($config[$service]['url'], $this->url);

      // Create the curl handle
      $curl_handles[$service] = curl_init();

      // set the url to make the curl request
      curl_setopt($curl_handles[$service], CURLOPT_URL, sprintf($config[$service]['url'], $this->url));

      // other necessary settings
      curl_setopt($curl_handles[$service], CURLOPT_HEADER, 0);
      curl_setopt($curl_handles[$service], CURLOPT_RETURNTRANSFER, 1);

      // set the timeout
      curl_setopt($curl_handles[$service], CURLOPT_TIMEOUT, 2);

      // set the http method
      if($config[$service]['method'] === 'POST') {
        curl_setopt($curl_handles[$service], CURLOPT_POST, 1);
      }

      // set the body and headers
      $headers = isset($config[$service]['headers']) ? $config[$service]['headers'] : array();
      $body = isset($config[$service]['body']) ? $config[$service]['body'] : NULL;

      if(isset($body)) {
        if(isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/json') {
          $data_string = json_encode($body);

          curl_setopt($curl_handles[$service], CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
          );

          curl_setopt($curl_handles[$service], CURLOPT_POSTFIELDS, $data_string);
        }
      }

      curl_multi_add_handle($multi_handle, $curl_handles[$service]);
    }

    if(count($curl_handles) > 0) {
      // execute the handles
      $running = NULL;
      do {
        curl_multi_exec($multi_handle, $running);
      } while($running > 0);

      // handle the responses
      foreach($curl_handles as $service => $handle) {
        $result = array(
          'body' => curl_multi_getcontent($handle),
          'response' => array(
            'code' => curl_getinfo($handle, CURLINFO_HTTP_CODE)
          ),
        );
        $callback = $config[$service]['callback'];
        $response[$service] = $this->$callback($result);
      }
      curl_multi_close($multi_handle);
    }
    return $response;
  }


}