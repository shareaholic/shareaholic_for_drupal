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
   * Outputs the actual html for the terms of service modal
   */
  public static function draw_tos_block() {
    if(!ShareaholicUtilities::has_accepted_terms_of_service()) {
      print(drupal_render(drupal_get_form('tos_modal_form')));
    } else if (!ShareaholicUtilities::get_option('api_key')) {
      print(drupal_render(drupal_get_form('failed_to_create_api_key_form')));
    }
  }

}

