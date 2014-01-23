<?php

/**
 * A class of static helper functions
 *
 */
class ShareaholicUtilities {
  /**
   * Returns whether the user has accepted our terms of service.
   *
   * @return bool
   */
  public static function has_accepted_terms_of_service() {
    return variable_get('shareaholic_has_accepted_tos');
  }


  /**
   * Accepts the terms of service.
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
      'disable_tracking' => 'off',
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
      "ar" => 1,
      "bg" => 2,
      "zh-hans" => 3,
      "zh-hant" => 4,
      "hr" => 5,
      "cs" => 6,
      "da" => 7,
      "nl" => 8,
      "en" => 9,
      "et" => 10,
      "fi" => 11,
      "fr" => 12,
      "de" => 13,
      "el" => 14,
      "he" => 15,
      "hu" => 16,
      "id" => 17,
      "it" => 18,
      "ja" => 19,
      "ko" => 20,
      "lv" => 21,
      "lt" => 22,
      "nn" => 23,
      "pl" => 24,
      "pt-pt" => 25,
      "ro" => 26,
      "ru" => 27,
      "sr" => 28,
      "sk" => 29,
      "sl" => 30,
      "es" => 31,
      "sv" => 32,
      "th" => 33,
      "tr" => 34,
      "uk" => 35,
      "vi" => 36,
    );
    return $language_id_map($GLOBAL['language']['language']);
  }


}