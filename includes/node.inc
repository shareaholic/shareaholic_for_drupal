<?php
/**
 * @file
 * Node module integration.
 */

/**
 * Implements hook_shareaholic_form_alter_alter() on behalf of node.module.
 */
function node_shareaholic_form_alter_alter(&$form, &$form_state, $form_id) {
  if ($form_id == 'node_type_form') {
    $form['shareaholic'] = array(
      '#type' => 'fieldset',
      '#title' => t('Shareaholic settings'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#group' => 'additional_settings',
      '#attached' => array(
        'js' => array(drupal_get_path('module', 'shareaholic') . '/scripts/node.js'),
      ),
    );

    $options = array();
    ctools_include('export');
    foreach (ctools_export_crud_load_all('shareaholic_profiles') as $profile) {
      if (empty($profile->disabled)) {
        $options[$profile->name] = $profile->name;
      }
    }
    $form['shareaholic']['node_shareaholic_profile'] = array(
      '#type' => 'select',
      '#title' => t('Profile'),
      '#empty_value' => '',
      '#options' => $options,
      '#default_value' => variable_get("node_shareaholic_profile_{$form['#node_type']->type}", 'default'),
    );

    $form['#submit'][] = 'shareaholic_node_type_form_submit';
  }
}

/**
 * Submit callback; clear field cache.
 */
function shareaholic_node_type_form_submit($form, &$form_state) {
  field_cache_clear();
}
