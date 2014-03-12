<?php
/**
 * Shareaholic Share Count
 *
 * @package shareaholic
 */

/**
 * An abstract class Share Counts to be extended
 *
 * @package shareaholic
 */
abstract class ShareaholicShareCount {

  protected $url;
  protected $services;

  public function __construct($url, $services) {
    $this->url = $url;
    $this->services = $services;
  }

  public function get_services_config() {
    return array(
      'facebook' => array(
        'url' => 'https://api.facebook.com/method/links.getStats?format=json&urls=%s',
        'method' => 'GET',
        'callback' => 'facebook_count_callback',
      ),
      'twitter' => array(
        'url' => 'http://cdn.api.twitter.com/1/urls/count.json?url=%s',
        'method' => 'GET',
        'callback' => 'twitter_count_callback',
      ),
      'linkedin' => array(
        'url' => 'http://www.linkedin.com/countserv/count/share?format=json&url=%s',
        'method' => 'GET',
        'callback' => 'linkedin_count_callback',
      ),
      'google_plus' => array(
        'url' => 'https://clients6.google.com/rpc',
        'method' => 'POST',
        'callback' => 'google_plus_count_callback',
      ),
      'delicious' => array(
        'url' => 'http://feeds.delicious.com/v2/json/urlinfo/data?url=%s',
        'method' => 'GET',
        'callback' => 'delicious_count_callback',
      ),
      'pinterest' => array(
        'url' => 'http://api.pinterest.com/v1/urls/count.json?url=%s&callback=f',
        'method' => 'GET',
        'callback' => 'pinterest_count_callback',
      ),
      'buffer' => array(
        'url' => 'https://api.bufferapp.com/1/links/shares.json?url=%s',
        'method' => 'GET',
        'callback' => 'buffer_count_callback',
      ),
      'stumbleupon' => array(
        'url' => 'http://www.stumbleupon.com/services/1.01/badge.getinfo?url=%s',
        'method' => 'GET',
        'callback' => 'stumbleupon_count_callback',
      ),
      'reddit' => array(
        'url' => 'http://buttons.reddit.com/button_info.json?url=%s',
        'method' => 'GET',
        'callback' => 'reddit_count_callback',
      ),
    );
  }


  /**
   * Callback function for facebook count API
   * Gets the facebook counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function facebook_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
      return 0;
    }
    $body = json_decode($response['body'], true);
    return isset($body[0]['total_count']) ? $body[0]['total_count'] : 0;
  }


  /**
   * Callback function for twitter count API
   * Gets the twitter counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function twitter_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
      return 0;
    }
    $body = json_decode($response['body'], true);
    return isset($body['count']) ? $body['count'] : 0;
  }


  /**
   * Callback function for linkedin count API
   * Gets the linkedin counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function linkedin_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
      return 0;
    }
    $body = json_decode($response['body'], true);
    return isset($body['count']) ? $body['count'] : 0;
  }


  /**
   * Callback function for google plus count API
   * Gets the google plus counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function google_plus_count_callback($response) {
    // TODO: implement this function
    return 0;
  }


  /**
   * Callback function for delicious count API
   * Gets the delicious counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function delicious_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
      return 0;
    }
    $body = json_decode($response['body'], true);
    return isset($body[0]['total_posts']) ? $body[0]['total_posts'] : 0;
  }


  /**
   * Callback function for pinterest count API
   * Gets the pinterest counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function pinterest_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
      return 0;
    }
    $response['body'] = substr($response['body'], 2, strlen($response['body']) - 3);
    $body = json_decode($response['body'], true);
    return isset($body['count']) ? $body['count'] : 0;
  }


  /**
   * Callback function for buffer count API
   * Gets the buffer share counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function buffer_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
      return 0;
    }
    $body = json_decode($response['body'], true);
    return isset($body['shares']) ? $body['shares'] : 0;
  }


  /**
   * Callback function for stumbleupon count API
   * Gets the stumbleupon counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function stumbleupon_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
      return 0;
    }
    $body = json_decode($response['body'], true);
    return isset($body['result']['views']) ? $body['result']['views'] : 0;
  }


  /**
   * Callback function for reddit count API
   * Gets the reddit counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function reddit_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
      return 0;
    }
    $body = json_decode($response['body'], true);
    return isset($body['data']['children'][0]['data']['ups']) ? $body['data']['children'][0]['data']['ups'] : 0;
  }


  /**
   * The abstract function to be implemented by its children
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
  public abstract function get_counts();


}