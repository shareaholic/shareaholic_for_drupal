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
      print(drupal_render(drupal_get_form('shareaholic_tos_modal_form')));
    } else if (!ShareaholicUtilities::get_option('api_key')) {
      print(drupal_render(drupal_get_form('shareaholic_failed_to_create_api_key_form')));
    }
  }

  /**
   * Show the terms of service notice on admin pages
   * except for shareaholic admin settings page
   */
  public function show_terms_of_service_notice() {
    if(ShareaholicUtilities::is_admin_page() &&
        !ShareaholicUtilities::is_shareaholic_settings_page() &&
        !ShareaholicUtilities::has_accepted_terms_of_service()) {
      //drupal_set_message(self::terms_of_service_html(), 'status', FALSE);
    }
  }

  /**
   * The html for the Terms of Service notice as a string
   * @return String The html for the notice as a string
   */
  private function terms_of_service_html() {
    $message = sprintf(t('Action required: You\'ve installed Shareaholic for Drupal.  We\'re ready when you are. %sGet started now &raquo;%s'), '<a href="/admin/config/content/shareaholic/settings" class="button">', '</a>');
    $html = <<< DOC
    <span>
      $message
    </span>
DOC;
    return $html;
  }

}

