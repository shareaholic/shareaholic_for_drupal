<?php
/**
 * @file
 * Views module integration.
 */

/**
 * Implements hook_views_api().
 */
function sexybookmarks_views_api() {
  return array('api' => 2.0);
}

/**
 * Implements hook_views_data().
 */
function sexybookmarks_views_data() {
  $data = array();

  $data['node']['sexybookmarks'] = array(
    'field' => array(
      'title' => t('SexyBookmarks'),
      'help' => t('SexyBookmarks'),
      'handler' => 'views_handler_field_node_sexybookmarks',
    ),
  );

  return $data;
}
