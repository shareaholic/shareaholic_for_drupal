<?php
/**
 * @file
 * Field UI module integration.
 */

/**
 * Implements hook_field_formatter_info().
 */
function shareaholic_field_formatter_info() {
  $formatters = array();

  $info = module_invoke_all('shareaholic_field_formatter_info');
  $formatters['shareaholic'] = array(
    'label' => t('Shareaholic'),
    'field types' => array_keys($info),
    'settings' => array('shareaholic_profile' => 'default'),
  );

  return $formatters;
}

/**
 * Implements hook_field_formatter_view().
 */
function shareaholic_field_formatter_view($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
  $element = array();
  $info = module_invoke_all('shareaholic_field_formatter_info');

  if (!empty($display['settings']['shareaholic_profile']) && isset($info[$field['type']]) && isset($info[$field['type']]['callback']) && function_exists($info[$field['type']]['callback'])) {
    foreach ($items as $delta => $item) {
      $data = $info[$field['type']]['callback']($item);
      $element[$delta] = array(
        '#theme' => 'shareaholic',
        '#url' => $data['url'],
        '#title' => $data['title'],
        '#profile' => $display['settings']['shareaholic_profile'],
      );
    }
  }

  return $element;
}

/**
 * Implements hook_field_formatter_settings_form().
 */
function shareaholic_field_formatter_settings_form($field, $instance, $view_mode, $form, &$form_state) {
  $display = $instance['display'][$view_mode];
  $element = array();

  $options = array();
  ctools_include('export');
  foreach (ctools_export_crud_load_all('shareaholic_profiles') as $profile) {
    if (empty($profile->disabled)) {
      $options[$profile->name] = $profile->name;
    }
  }
  $element['shareaholic_profile'] = array(
    '#type' => 'select',
    '#title' => t('Shareaholic profile'),
    '#empty_value' => '',
    '#options' => $options,
    '#default_value' => !empty($display['settings']['shareaholic_profile']) ? $display['settings']['shareaholic_profile'] : 'default',
  );

  return $element;
}

/**
 * Implements hook_field_formatter_settings_summary().
 */
function shareaholic_field_formatter_settings_summary($field, $instance, $view_mode) {
  $display = $instance['display'][$view_mode];
  return t('Profile: !profile', array('!profile' => !empty($display['settings']['shareaholic_profile']) ? $display['settings']['shareaholic_profile'] : t('None')));
}

/**
 * Implements hook_field_extra_fields().
 */
function shareaholic_field_extra_fields() {
  $extra = array();

  ctools_include('export');
  foreach (field_info_bundles() as $entity => $bundles) {
    foreach (array_keys($bundles) as $bundle) {
      $profile = ctools_export_crud_load('shareaholic_profiles', variable_get("{$entity}_shareaholic_profile_{$bundle}", 'default'));
      if (!empty($profile)) {
        $extra[$entity][$bundle] = array(
          'display' => array(
            'shareaholic' => array(
              'label' => t('Shareaholic'),
              'description' => t('Shareaholic'),
              'weight' => 100,
            ),
          ),
        );
      }
    }
  }

  return $extra;
}

/**
 * Implements hook_entity_view().
 */
function shareaholic_entity_view($entity, $type, $view_mode, $langcode) {
  ctools_include('export');
  $profile = ctools_export_crud_load('shareaholic_profiles', variable_get("{$type}_shareaholic_profile_{$entity->content['#bundle']}", 'default'));
  if (!empty($profile)) {
    $entity->content['shareaholic'] = array(
      '#markup' => theme('shareaholic', array(
        'url' => isset($entity->uri['path']) ? url($entity->uri['path'], array('absolute' => TRUE)) : '',
        'title' => isset($entity->title) ? $entity->title : '',
        'profile' => $profile->name,
      )),
      '#weight' => 100,
    );
  }
}
