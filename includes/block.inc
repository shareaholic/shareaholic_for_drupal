<?php
/**
 * @file
 * Block module integration.
 */

/**
 * Implements hook_block_info().
 */
function shareaholic_block_info() {
  $blocks = array();

  $blocks['current'] = array(
    'info' => t('Shareaholic: Current URL'),
    'cache' => DRUPAL_NO_CACHE,
  );

  $blocks['site'] = array(
    'info' => t('Shareaholic: Site URL'),
    'cache' => DRUPAL_NO_CACHE,
  );

  return $blocks;
}

/**
 * Implements hook_block_configure().
 */
function shareaholic_block_configure($delta) {
  $form = array();

  $options = array();
  ctools_include('export');
  foreach (ctools_export_crud_load_all('shareaholic_profiles') as $profile) {
    if (empty($profile->disabled)) {
      $options[$profile->name] = $profile->name;
    }
  }
  $form['profile'] = array(
    '#type' => 'select',
    '#title' => t('Shareaholic profile'),
    '#empty_value' => '',
    '#options' => $options,
    '#default_value' => variable_get("shareaholic_block_{$delta}_profile", 'default'),
  );

  return $form;
}

/**
 * Implements hook_block_save().
 */
function shareaholic_block_save($delta = 0, $edit = array()) {
  variable_set("shareaholic_block_{$delta}_profile", $edit['profile']);
}

/**
 * Implements hook_block_view().
 */
function shareaholic_block_view($delta) {
  if (function_exists($function = "shareaholic_block_{$delta}_view")) {
    return $function();
  }
}

function shareaholic_block_current_view() {
  $profile = variable_get('shareaholic_block_current_profile', 'default');
  if (!empty($profile)) {
    return array(
      'subject' => t('Shareaholic'),
      'content' => theme('shareaholic', array(
        'profile' => $profile,
        'id' => 'block-shareaholic-current',
      ))
    );
  }
}

function shareaholic_block_site_view() {
  $profile = variable_get('shareaholic_block_site_profile', 'default');
  if (!empty($profile)) {
    return array(
      'subject' => t('Shareaholic'),
      'content' => theme('shareaholic', array(
        'profile' => $profile,
        'url' => url(NULL, array('absolute' => TRUE)),
        'title' => variable_get('site_name', 'Drupal'),
        'id' => 'block-shareaholic-site',
      ))
    );
  }
}
