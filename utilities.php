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


}