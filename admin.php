<?php
/**
 * This file holds the ShareaholicAdmin class.
 *
 * @package shareaholic
 */

/**
 * This class takes care of all of the admin interface.
 *
 * @package shareaholic
 */
class ShareaholicAdmin {


  /**
   * Outputs the actual html for either the terms_of_service modal or the
   * failed_create_api_key modal depending on what is in the database
   *
   * @return String html output for the modals
   */
  public static function draw_modal_popup() {
    if(!ShareaholicUtilities::has_accepted_terms_of_service()) {
      print(drupal_render(drupal_get_form('tos_modal_form')));
    } else if (!ShareaholicUtilities::get_option('api_key')) {
      print(drupal_render(drupal_get_form('failed_to_create_api_key_form')));
    }
  }

}

