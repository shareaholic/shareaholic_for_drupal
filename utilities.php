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
}