<?php
/**
 * @file
 * Field UI module integration.
 */

/**
 * Implements hook_field_formatter_info().
 */
function sexybookmarks_field_formatter_info() {
  $formatters = array();

  $info = module_invoke_all('sexybookmarks_field_formatter_info');
  $formatters['sexybookmarks'] = array(
    'label' => t('SexyBookmarks'),
    'field types' => array_keys($info),
    'settings' => array('sexybookmarks_profile' => 'default'),
  );

  return $formatters;
}

/**
 * Implements hook_field_formatter_view().
 */
function sexybookmarks_field_formatter_view($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
  $element = array();
  $info = module_invoke_all('sexybookmarks_field_formatter_info');

  if (!empty($display['settings']['sexybookmarks_profile']) && isset($info[$field['type']]) && isset($info[$field['type']]['callback']) && function_exists($info[$field['type']]['callback'])) {
    foreach ($items as $delta => $item) {
      $data = $info[$field['type']]['callback']($item);
      $element[$delta] = array(
        '#theme' => 'sexybookmarks',
        '#url' => $data['url'],
        '#title' => $data['title'],
        '#profile' => $display['settings']['sexybookmarks_profile'],
      );
    }
  }

  return $element;
}

/**
 * Implements hook_field_formatter_settings_form().
 */
function sexybookmarks_field_formatter_settings_form($field, $instance, $view_mode, $form, &$form_state) {
  $display = $instance['display'][$view_mode];
  $element = array();

  $options = array();
  ctools_include('export');
  foreach (ctools_export_crud_load_all('sexybookmarks_profiles') as $profile) {
    if (empty($profile->disabled)) {
      $options[$profile->name] = $profile->name;
    }
  }
  $element['sexybookmarks_profile'] = array(
    '#type' => 'select',
    '#title' => t('SexyBookmarks profile'),
    '#empty_value' => '',
    '#options' => $options,
    '#default_value' => !empty($display['settings']['sexybookmarks_profile']) ? $display['settings']['sexybookmarks_profile'] : 'default',
  );

  return $element;
}

/**
 * Implements hook_field_formatter_settings_summary().
 */
function sexybookmarks_field_formatter_settings_summary($field, $instance, $view_mode) {
  $display = $instance['display'][$view_mode];
  return t('Profile: !profile', array('!profile' => !empty($display['settings']['sexybookmarks_profile']) ? $display['settings']['sexybookmarks_profile'] : t('None')));
}

/**
 * Implements hook_field_extra_fields().
 */
function sexybookmarks_field_extra_fields() {
  $extra = array();

  ctools_include('export');
  foreach (field_info_bundles() as $entity => $bundles) {
    foreach (array_keys($bundles) as $bundle) {
      $profile = ctools_export_crud_load('sexybookmarks_profiles', variable_get("{$entity}_sexybookmarks_profile_{$bundle}", 'default'));
      if (!empty($profile)) {
        $extra[$entity][$bundle] = array(
          'display' => array(
            'sexybookmarks' => array(
              'label' => t('SexyBookmarks'),
              'description' => t('SexyBookmarks'),
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
function sexybookmarks_entity_view($entity, $type, $view_mode, $langcode) {
  ctools_include('export');
  $profile = ctools_export_crud_load('sexybookmarks_profiles', variable_get("{$type}_sexybookmarks_profile_{$entity->content['#bundle']}", 'default'));
  if (!empty($profile)) {
    $entity->content['sexybookmarks'] = array(
      '#markup' => theme('sexybookmarks', array(
        'url' => isset($entity->uri['path']) ? url($entity->uri['path'], array('absolute' => TRUE)) : '',
        'title' => isset($entity->title) ? $entity->title : '',
        'profile' => $profile->name,
      )),
      '#weight' => 100,
    );
  }
}
