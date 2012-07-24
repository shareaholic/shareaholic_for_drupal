<?php
/**
 * @file
 * Shorten module integration.
 */

/**
 * Implements hook_shareaholic_form_alter_alter() on behalf of shorten.module.
 */
function shorten_shareaholic_form_alter_alter(&$form, &$form_state, $form_id) {
  if ($form_id == 'ctools_export_ui_edit_item_form') {
    $form['config']['settings']['shortener']['#options'] = array_merge(
      array('drupal_shorten' => t('Shorten module')),
      $form['config']['settings']['shortener']['#options']
    );
  }
}

/**
 * Implements hook_shareaholic_config_alter() on behalf of shorten.module.
 */
function shorten_shareaholic_config_alter(&$config, &$variables) {
  if ($config['shortener'] == 'drupal_shorten') {
    $config['shortener'] = 'none';
    $variables['url'] = shorten_url($variables['url']);
  }
}
