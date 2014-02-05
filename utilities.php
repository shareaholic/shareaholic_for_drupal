<?php

/**
 * A class of static helper functions
 *
 */
class ShareaholicUtilities {
  /**
   * Returns whether the user has accepted our terms of service.
   * If the user has accepted, return true otherwise return NULL
   *
   * @return mixed (true or NULL)
   */
  public static function has_accepted_terms_of_service() {
    return variable_get('shareaholic_has_accepted_tos');
  }


  /**
   * Accepts the terms of service by setting the variable to true
   */
  public static function accept_terms_of_service() {
    variable_set('shareaholic_has_accepted_tos', true);
  }


  /**
   * Returns the defaults for shareaholic settings
   *
   * @return array
   */
  private static function defaults() {
    return array(
      'disable_analytics' => 'off',
      'api_key' => '',
      'verification_key' => '',
    );
  }

  /**
   * Just a wrapper around variable_get to
   * get the shareaholic settings. If the settings
   * have not been set it will return an array of defaults.
   *
   * @return array
   */
  public static function get_settings() {
    return variable_get('shareaholic_settings', self::defaults());
  }


  /**
   * Wrapper for wordpress's get_option: for Drupal
   *
   * @param string $option
   *
   * @return mixed
   */
  public static function get_option($option) {
    $settings = self::get_settings();
    return (isset($settings[$option]) ? $settings[$option] : array());
  }

  /**
   * Update multiple keys of the settings object
   * Works like the Wordpress function for Shareaholic
   *
   * @param  array $array an array of options to update
   * @return bool
   */
  public static function update_options($array) {
    $old_settings = self::get_settings();
    $new_settings = self::array_merge_recursive_distinct($old_settings, $array);
    variable_set('shareaholic_settings', $new_settings);
  }


  /**
   * Deletes the settings option
   */
  public static function destroy_settings() {
    variable_del('shareaholic_settings');
  }


  /**
   * Returns the site's url stripped of protocol.
   *
   * @return string
   */
  public static function site_url() {
    return preg_replace('/https?:\/\//', '', $GLOBALS['base_url']);
  }

  /**
   * Returns the site's name
   *
   * @return string
   */
  public static function site_name() {
    return variable_get('site_name', $GLOBALS['base_url']);
  }

  /**
   * Returns the site's primary locale / language
   *
   * @return string
   */
  public static function site_language() {
    $language_id_map = array(
      "ar" => 1, // Arabic
      "bg" => 2, // Bulgarian
      "zh-hans" => 3, // Chinese (Simplified)
      "zh-hant" => 4, // Chinese (Traditional)
      "hr" => 5, // Croatian
      "cs" => 6, // Czech
      "da" => 7, // Danish
      "nl" => 8, // Netherlands
      "en" => 9, // English
      "et" => 10, // Estonian
      "fi" => 11, // Finnish
      "fr" => 12,  // French
      "de" => 13,  // German
      "el" => 14,  // Greek
      "he" => 15,  // Hebrew
      "hu" => 16,  // Hungarian
      "id" => 17,  // Indonesian
      "it" => 18,  // Italian
      "ja" => 19,  // Japanese
      "ko" => 20,  // Korean
      "lv" => 21,  // Lativan
      "lt" => 22,  // Lithuanian
      "nn" => 23,  // Norwegian
      "pl" => 24,  // Poland
      "pt-pt" => 25, // Portuguese
      "ro" => 26,    // Romanian
      "ru" => 27,    // Russian
      "sr" => 28,    // Serbian
      "sk" => 29,    // Slovak
      "sl" => 30,    // Slovenian
      "es" => 31,    // Spanish
      "sv" => 32,    // Swedish
      "th" => 33,    // Thai
      "tr" => 34,    // Turkish
      "uk" => 35,    // Ukrainian
      "vi" => 36,    // Vietnamese
    );
    $language = $GLOBALS['language']->language;
    return isset($language_id_map[$language]) ? $language_id_map[$language] : NULL;
  }

  /**
   * Returns the api key or creates a new one.
   *
   * It first checks the database. If the key is not
   * found (or is an empty string or empty array or
   * anything that evaluates to false) then we will
   * attempt to make a new one by POSTing to the
   * anonymous configuration endpoint
   *
   * @return string
   */
  public static function get_or_create_api_key() {
    $api_key = self::get_option('api_key');
    if ($api_key) {
      return $api_key;
    }

    $verification_key = md5(mt_rand());
    $post_data = array(
      'configuration_publisher' => array(
        'verification_key' => $verification_key,
        'site_name' => self::site_name(),
        'domain' => self::site_url(),
        'platform_id' => '2',
        'language_id' => self::site_language(),
        'shortener' => 'shrlc',
        'recommendations_attributes' => array(
          'locations_attributes' => array(
            array('name' => 'post_below_content'),
            array('name' => 'page_below_content'),
          )
        ),
        'share_buttons_attributes' => array(
          'locations_attributes' => array(
            array('name' => 'post_below_content', 'counter' => 'badge-counter'),
            array('name' => 'page_below_content', 'counter' => 'badge-counter'),
            array('name' => 'index_below_content', 'counter' => 'badge-counter'),
            array('name' => 'category_below_content', 'counter' => 'badge-counter')
          )
        )
      )
    );

    $response = drupal_http_request(SHAREAHOLIC_URL . '/publisher_tools/anonymous', array(
      'method' => 'POST',
      'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
      'data' => http_build_query($post_data)
    ));

    if(self::has_bad_response($response, 'FailedToCreateApiKey', true)) {
      return NULL;
    }
    $response = (array) $response;
    $json_response = json_decode($response['data'], true);
    self::update_options(array(
      'api_key' => $json_response['api_key'],
      'verification_key' => $verification_key,
      'location_name_ids' => $json_response['location_name_ids']
    ));

    if (isset($json_response['location_name_ids']) && is_array($json_response['location_name_ids'])) {
      //ShareaholicUtilities::turn_on_locations($response['body']['location_name_ids']);
    } else {
      self:log('FailedToCreateApiKey: no location name ids the response was: ' . $response['data']);
    }
  }

  /**
   * Checks bad response and logs errors if any
   *
   * @return boolean
   */
  public static function has_bad_response($response, $type, $json_parse = FALSE) {
    if(!$response) {
      self::log($type . ': There was no response');
      return true;
    }
    $response = (array) $response;
    if(isset($response['error'])) {
      self::log($type . ': There was an error: ' . $response['error']);
      return true;
    }
    if(!($response['code'] >= 200 && $response['code'] < 210)) {
      self::log($type . ': The server responded with code ' . $response['code']);
      return true;
    }
    if($json_parse && json_decode($response['data']) === NULL) {
      self::log($type . ': Could not parse JSON. The response was: ' . $response['data']);
      return true;
    }
    return false;
  }

  /**
   * Log the errors in the database if debug flag is set to true
   *
   */
  public static function log($message) {
    if(SHAREAHOLIC_DEBUG) {
      watchdog('Shareaholic', $message);
    }
  }

  /**
   * Direct copy of the wordpress util function
   * If the two arrays have the same key, the value from array2 overrides
   * the value on array1
   *
   * @param  array $array1
   * @param  array $array2
   * @return array
   */
  public static function array_merge_recursive_distinct ( array &$array1, array &$array2 )
  {
    $merged = $array1;

    foreach ( $array2 as $key => &$value )
    {
      if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
      {
        if (empty($value)) {
          $merged[$key] = array();
        } else {
          $merged [$key] = self::array_merge_recursive_distinct ( $merged [$key], $value );
        }
      }
      else
      {
        $merged [$key] = $value;
      }
    }

    return $merged;
  }

  /**
   * Returns the appropriate asset path for something from our
   * rails app based on URL constant.
   *
   * @param string $asset
   * @return string
   */
  public static function asset_url($asset) {
    if (preg_match('/spreadaholic/', SHAREAHOLIC_URL)) {
      return 'http://spreadaholic.com:8080/assets/' . $asset;
    } elseif (preg_match('/stageaholic/', SHAREAHOLIC_URL)) {
      return '//d2062rwknz205x.cloudfront.net/assets/' . $asset;
    } else {
      return '//dsms0mj1bbhn4.cloudfront.net/assets/' . $asset;
    }
  }

  /**
   * Check if the installation has accepted ToS and we created an apikey
   *
   */
  public static function has_tos_and_apikey() {
    return (ShareaholicUtilities::has_accepted_terms_of_service() &&
              ShareaholicUtilities::get_option('api_key'));
  }

  /**
   * Gets the current version of this module
   */
  public static function get_version() {
    $path = drupal_get_path('module', 'shareaholic') . '/shareaholic.info';
    $info = drupal_parse_info_file($path);
    return $info['version'];
  }

  /**
   * Checks if the current page is an admin page
   * @return Boolean (actually 1, 0, or FALSE)
   */
  public static function is_admin_page() {
    return preg_match('/admin/', request_uri());
  }

  /**
   * Checks if the current page is the settings page
   * @return Boolean (actually 1, 0, or FALSE)
   */
  public static function is_shareaholic_settings_page() {
    return preg_match('/admin\/config\/content\/shareaholic/', request_uri());
  }

}