<?php
/**
 * @file
 * CTools module integration.
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function shareaholic_ctools_plugin_api() {
  list($module, $api) = func_get_args();
  if ($module == 'shareaholic' && $api == 'default_shareaholic_profiles') {
    return array('version' => 2);
  }
  if ($module == 'boxes' && $api == 'box') {
    return array('version' => 1);
  }
}


/**
 * Implements hook_ctools_plugin_directory().
 */
function shareaholic_ctools_plugin_directory($module, $plugin) {
  if ($module == 'ctools' && !empty($plugin)) {
    return "plugins/{$plugin}";
  }
}
