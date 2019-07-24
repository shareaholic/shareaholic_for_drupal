<?php

namespace Drupal\shareaholic\Controller;

use Drupal\Console\Bootstrap\Drupal;
use Drupal\Core\Controller\ControllerBase;

class ShareaholicController extends ControllerBase {

  const MODULE_VERSION = '8.x-1.0';

  const URL = 'https://www.shareaholic.com';

  const API_URL = 'https://www.shareaholic.com';

  const CM_API_URL = 'http://localhost:3000';


  public function configPage() {

    $page = [];

    $page['#markup'] = '';

    return $page;

  }

  /**
   * Returns whether the user has accepted our terms of service.
   * If the user has accepted, return true otherwise return NULL
   *
   * @return mixed (true or NULL)
   */
  public function has_accepted_terms_of_service() {
    return variable_get('shareaholic_has_accepted_tos');
  }

  /**
   * Accepts the terms of service by setting the variable to true
   */
  public function accept_terms_of_service() {
    variable_set('shareaholic_has_accepted_tos', TRUE);
    //    ShareaholicUtilities::log_event('AcceptedToS');
  }

  /**
   * Returns the defaults for shareaholic settings
   *
   * @return array
   */
  private static function defaults() {
    return [
      'disable_internal_share_counts_api' => 'on',
      'api_key' => '',
      'verification_key' => '',
    ];
  }

  /**
   * Just a wrapper around variable_get to
   * get the shareaholic settings. If the settings
   * have not been set it will return an array of defaults.
   *
   * @return array
   */
  public function get_settings() {
    $settings = \Drupal::config('shareaholic.settings');
    return $settings;
  }

  /**
   * Wrapper for wordpress's get_option: for Drupal
   *
   * @param string $option
   *
   * @return mixed
   */
  public function get_option($option) {
    $settings = \Drupal::config('shareaholic.settings');

    if (!empty($settings->get($option))) {
      $value = $settings->get($option);
    }
    else {
      $value = [];
    }

    return $value;
  }

  /**
   * Gets the current version of this module
   */
  public static function get_version() {
    return self::MODULE_VERSION;
  }

  /**
   * Sets the current version of this module in the database
   */
  public static function set_version($version) {
    self::update_options(['version' => $version]);
  }

  /**
   * Give back only the request keys from an array. The first
   * argument is the array to be sliced, and after that it can
   * either be a variable-length list of keys or one array of keys.
   *
   * @param array $array
   * @param Mixed ... can be either one array or many keys
   *
   * @return array
   */
  public static function associative_array_slice($array) {
    $keys = array_slice(func_get_args(), 1);
    if (func_num_args() == 2 && is_array($keys[0])) {
      $keys = $keys[0];
    }

    $result = [];

    foreach ($keys as $key) {
      $result[$key] = $array[$key];
    }

    return $result;
  }


  /**
   * Passed an array of location names mapped to ids per app.
   *
   * @param array $array
   */
  public static function turn_on_locations($array, $turn_off_array = []) {
    if (is_array($array)) {
      foreach ($array as $app => $ids) {
        if (is_array($ids)) {
          foreach ($ids as $name => $id) {
            self::update_options([
              $app => [$name => 'on'],
            ]);
          }
        }
      }
    }

    if (is_array($turn_off_array)) {
      foreach ($turn_off_array as $app => $ids) {
        if (is_array($ids)) {
          foreach ($ids as $name => $id) {
            self::update_options([
              $app => [$name => 'off'],
            ]);
          }
        }
      }
    }
  }


  /**
   * Update multiple keys of the settings object
   * Works like the Wordpress function for Shareaholic
   *
   * @param array $array an array of options to update
   *
   * @return bool
   */
  public function update_options($array) {

    $settings = \Drupal::configFactory()->getEditable('shareaholic.settings');

    foreach ($array as $key => $setting) {
      $settings->set($key, $setting)->save();
    }

  }


  public function generateKey() {

    $verification_key = md5(mt_rand());
    $page_types = self::page_types();

    $turned_on_recommendations_locations = self::get_default_rec_on_locations();
    $turned_off_recommendations_locations = self::get_default_rec_off_locations();
    $turned_on_share_buttons_locations = self::get_default_sb_on_locations();
    $turned_off_share_buttons_locations = self::get_default_sb_off_locations();

    $share_buttons_attributes = array_merge($turned_on_share_buttons_locations, $turned_off_share_buttons_locations);
    $recommendations_attributes = array_merge($turned_on_recommendations_locations, $turned_off_recommendations_locations);

    $post_data = [
      'configuration_publisher' => [
        'verification_key' => $verification_key,
        'site_name' => self::site_name(),
        'domain' => self::site_url(),
        'platform_id' => '2',
        'language_id' => self::site_language(),
        'shortener' => 'shrlc',
        'recommendations_attributes' => [
          'locations_attributes' => $recommendations_attributes,
        ],
        'share_buttons_attributes' => [
          'locations_attributes' => $share_buttons_attributes,
        ],
      ],
    ];

    $client = \Drupal::httpClient();
    $apiUrl = self::API_URL . '/publisher_tools/anonymous';
    $settings = [
      'headers' => [
        'Content-type' => 'application/vnd.api+json',
      ],
      'body' => json_encode($post_data),
    ];

    $data = [];
    try {
      $response = $client->post($apiUrl, $settings);
      $data = (string) $response->getBody();

      if (empty($data)) {
        return FALSE;
      }

    } catch (RequestException $e) {

      return FALSE;

    }

    if (!empty($data)) {

      $json_response = json_decode($data, TRUE);

      self::update_options([
        'version' => self::get_version(),
        'api_key' => $json_response['api_key'],
        'verification_key' => $verification_key,
        // 'location_name_ids' => $json_response['location_name_ids'],
      ]);
    }

    if (isset($json_response['location_name_ids']) && is_array($json_response['location_name_ids']) && isset($json_response['location_name_ids']['recommendations']) && isset($json_response['location_name_ids']['share_buttons'])) {
      //      self::set_default_location_settings($json_response['location_name_ids']);
      //      ShareaholicContentManager::single_domain_worker();
    }
    else {
      //      ShareaholicUtilities::log_event('FailedToCreateApiKey', array('reason' => 'no location name ids the response was: ' . $response['data']));
    }

    self::log_event('AcceptedToS');


    $page = [];

    //    $page['#markup']['content'] = '<pre>' . print_r(json_decode($data)) . '</pre>';

    return $page;

  }

  /**
   * Returns the site's name
   *
   * @return string
   */
  public function site_name() {
    //    return \Drupal::state()->get('site_name');
    return \Drupal::config('system.site')->get('name');
  }

  /**
   * Returns the site's url stripped of protocol.
   *
   * @return string
   */
  public function site_url() {
    return \Drupal::request()->getHost();
  }

  /**
   * Returns the site's primary locale / language
   *
   * @return string
   */
  public function site_language() {
    $language_id_map = [
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
    ];
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    return isset($language_id_map[$language]) ? $language_id_map[$language] : NULL;
  }


  /**
   * Get all the available page types
   * Insert the teaser mode as a page type
   *
   * @return Array list of page types
   */
  public function page_types() {

    $page_types = \Drupal\node\Entity\NodeType::loadMultiple();

    //        $teaser = new stdClass();
    //        $teaser->name = 'Teaser';
    //        $teaser->type = 'teaser';
    //        $page_types['shareaholic_custom_type'] = $teaser;

    return $page_types;
  }

  /**
   * Get recommendations locations that should be turned on by default
   *
   * @return {Array}
   */
  public function get_default_rec_on_locations() {
    $page_types = self::page_types();
    $turned_on_recommendations_locations = [];

    foreach ($page_types as $key => $page_type) {

      $page_type_name = str_replace(' ', '_', strtolower($page_type->label()));
      if ($page_type_name === 'article' || $page_type_name === 'page') {
        $turned_on_recommendations_locations[] = [
          'name' => $page_type_name . '_below_content',
        ];
      }
    }

    return $turned_on_recommendations_locations;
  }

  /**
   * Get recommendations locations that should be turned off by default
   *
   * @return {Array}
   */
  public function get_default_rec_off_locations() {
    $page_types = self::page_types();
    $turned_off_recommendations_locations = [];

    foreach ($page_types as $key => $page_type) {
      $page_type_name = str_replace(' ', '_', strtolower($page_type->label()));
      if ($page_type_name !== 'article' && $page_type_name !== 'page') {
        $turned_off_recommendations_locations[] = [
          'name' => $page_type_name . '_below_content',
        ];
      }
    }

    return $turned_off_recommendations_locations;
  }

  /**
   * Get share buttons locations that should be turned on by default
   *
   * @return {Array}
   */
  public function get_default_sb_on_locations() {
    $page_types = self::page_types();
    $turned_on_share_buttons_locations = [];

    foreach ($page_types as $key => $page_type) {
      $page_type_name = str_replace(' ', '_', strtolower($page_type->label()));

      $turned_on_share_buttons_locations[] = [
        'name' => $page_type_name . '_below_content',
      ];
    }

    return $turned_on_share_buttons_locations;
  }

  /**
   * Get share buttons locations that should be turned off by default
   *
   * @return {Array}
   */
  public function get_default_sb_off_locations() {
    $page_types = self::page_types();
    $turned_off_share_buttons_locations = [];

    foreach ($page_types as $key => $page_type) {
      $page_type_name = str_replace(' ', '_', strtolower($page_type->label()));

      $turned_off_share_buttons_locations[] = [
        'name' => $page_type_name . '_above_content',
      ];
    }

    return $turned_off_share_buttons_locations;
  }

  /**
   * Given an object, set the default on/off locations
   * for share buttons and recommendations
   *
   */
  public static function set_default_location_settings($location_name_ids) {
    $turned_on_share_buttons_locations = self::get_default_sb_on_locations();
    $turned_off_share_buttons_locations = self::get_default_sb_off_locations();

    $turned_on_recommendations_locations = self::get_default_rec_on_locations();
    $turned_off_recommendations_locations = self::get_default_rec_off_locations();

    $turned_on_share_buttons_keys = [];
    foreach ($turned_on_share_buttons_locations as $loc) {
      $turned_on_share_buttons_keys[] = $loc['name'];
    }

    $turned_on_recommendations_keys = [];
    foreach ($turned_on_recommendations_locations as $loc) {
      $turned_on_recommendations_keys[] = $loc['name'];
    }

    $turned_off_share_buttons_keys = [];
    foreach ($turned_off_share_buttons_locations as $loc) {
      $turned_off_share_buttons_keys[] = $loc['name'];
    }

    $turned_off_recommendations_keys = [];
    foreach ($turned_off_recommendations_locations as $loc) {
      $turned_off_recommendations_keys[] = $loc['name'];
    }

    $turn_on = [
      'share_buttons' => self::associative_array_slice($location_name_ids['share_buttons'], $turned_on_share_buttons_keys),
      'recommendations' => self::associative_array_slice($location_name_ids['recommendations'], $turned_on_recommendations_keys),
    ];

    $turn_off = [
      'share_buttons' => self::associative_array_slice($location_name_ids['share_buttons'], $turned_off_share_buttons_keys),
      'recommendations' => self::associative_array_slice($location_name_ids['recommendations'], $turned_off_recommendations_keys),
    ];

    self::turn_on_locations($turn_on, $turn_off);
  }

  /**
   * Direct copy of the wordpress util function
   * If the two arrays have the same key, the value from array2 overrides
   * the value on array1
   *
   * @param array $array1
   * @param array $array2
   *
   * @return array
   */
  public static function array_merge_recursive_distinct(array &$array1, array &$array2) {
    $merged = $array1;

    foreach ($array2 as $key => &$value) {
      if (is_array($value) && isset ($merged [$key]) && is_array($merged [$key])) {
        if (empty($value)) {
          $merged[$key] = [];
        }
        else {
          $merged [$key] = self::array_merge_recursive_distinct($merged [$key], $value);
        }
      }
      else {
        $merged [$key] = $value;
      }
    }

    return $merged;
  }

  /**
   * This is a wrapper for the Event API
   *
   * @param string $event_name the name of the event
   * @param array $extra_params any extra data points to be included
   */
  public function log_event($event_name = 'Default', $extra_params = FALSE) {

    $event_metadata = [
      'plugin_version' => self::get_version(),
      'api_key' => self::get_option('api_key'),
      'domain' => self::site_url(),
      'language' => \Drupal::languageManager()->getCurrentLanguage()->getId(),
      //      'stats' => self::get_stats(),
      'diagnostics' => [
        'php_version' => phpversion(),
        'drupal_version' => \Drupal::VERSION,
        //        'theme' => variable_get('theme_default', $GLOBALS['theme']),
        //        'active_plugins' => module_list(),
      ],
      'features' => [
        //        'share_buttons' => self::get_option('share_buttons'),
        //        'recommendations' => self::get_option('recommendations'),
      ],
    ];

    if ($extra_params) {
      $event_metadata = array_merge($event_metadata, $extra_params);
    }

    dpm(json_encode($event_metadata));

//    $event_api_url = self::API_URL . '/api/events';
    $event_params = [
      'name' => "Drupal:" . $event_name,
      'data' => json_encode($event_metadata),
    ];
//    $options = [
//      'method' => 'POST',
//      'headers' => ['Content-Type' => 'application/json'],
//      'body' => $event_params,
//    ];

    $client = \Drupal::httpClient();
    $apiUrl = self::API_URL . '/api/events';
    $settings = [
      'headers' => [
        'Content-type' => 'application/vnd.api+json',
      ],
      'body' => json_encode($event_params),
    ];

    $data = [];
    try {
      $response = $client->post($apiUrl, $settings);
      $data = (string) $response->getBody();

      if (empty($data)) {
        return FALSE;
      }

    } catch (RequestException $e) {

      return FALSE;

    }

    dpm($data);
    //    ShareaholicHttp::send($event_api_url, $options, TRUE);
  }

}
