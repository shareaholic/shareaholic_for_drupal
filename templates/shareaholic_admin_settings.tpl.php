<?php
  $module_path = drupal_get_path('module', 'shareaholic');
  drupal_add_css($module_path . '/assets/css/reveal.css', array('group' => CSS_DEFAULT));
  drupal_add_css($module_path . '/assets/css/main.css', array('group' => CSS_DEFAULT));
  drupal_add_js($module_path . '/assets/js/jquery_custom.js', array('group' => JS_DEFAULT));
  drupal_add_js($module_path . '/assets/js/jquery_ui_custom.js', array('group' => JS_DEFAULT));
  drupal_add_js($module_path . '/assets/js/jquery.reveal.modified.js', array('group' => JS_DEFAULT));
  drupal_add_js($module_path . '/assets/js/main.js', array('group' => JS_DEFAULT));
  print '<div id="shareaholic-form-container">';
  print(drupal_render(drupal_get_form('shareaholic_advanced_settings_form')));
  print(drupal_render(drupal_get_form('shareaholic_reset_plugin_form')));
  ShareaholicAdmin::draw_modal_popup();
  print '</div>';
?>