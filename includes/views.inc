<?php
/**
 * @file
 * Views module integration.
 */

/**
 * Implements hook_views_api().
 */
function shareaholic_views_api() {
  return array('api' => 2.0);
}

/**
 * Implements hook_views_data().
 */
function shareaholic_views_data() {
  $data = array();

  $data['node']['shareaholic'] = array(
    'field' => array(
      'title' => t('Shareaholic'),
      'help' => t('Shareaholic'),
      'handler' => 'views_handler_field_node_shareaholic',
    ),
  );

  return $data;
}
