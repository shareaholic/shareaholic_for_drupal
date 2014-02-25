<?php

  /**
   * @file
   *
   * This file is responsible for the advanced
   * settings form (rendering/and handling submit)
   */

  /**
   * The form object for the advanced settings
   * The form will have input for:
   * - disable analytics (checkbox, default unchecked)
   *
   */
  function shareaholic_advanced_settings_form() {
    $disable_analytics_checked = ShareaholicUtilities::get_option('disable_analytics');
    $form['advanced_settings'] = array(
      '#prefix' => '<fieldset class="app"><legend><h2>' . t('Advanced') . '</h2></legend>',
      '#suffix' => '</fieldset>',
    );
    $form['advanced_settings']['disable_analytics'] = array(
      '#type' => 'checkbox',
      '#title' => t('Disable Analytics (it is recommended NOT to disable analytics)'),
    );
    $form['advanced_settings']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save Changes')
    );
    $form['advanced_settings']['submit']['#attributes']['class'][] = 'settings';
    $form['advanced_settings']['submit']['#attributes']['onclick'][] = 'this.value="Saving Settings..."';
    if($disable_analytics_checked === 'on') {
      $form['advanced_settings']['disable_analytics']['#attributes'] = array('checked' => 'checked');
    }
    return $form;
  }

  function shareaholic_advanced_settings_form_submit($form, &$form_state) {
    if(ShareaholicUtilities::has_tos_and_apikey()) {
      $checked = ($form_state['values']['disable_analytics'] === 1) ? 'on' : 'off';
      ShareaholicUtilities::update_options(array(
        'disable_analytics' => $checked
      ));
    drupal_set_message(t('Settings Saved: please clear your cache.'), 'status');
    }
  }
