<?php
  $module_path = drupal_get_path('module', 'shareaholic');
  drupal_add_css($module_path . '/assets/css/reveal.css', array('group' => CSS_DEFAULT));
  drupal_add_css($module_path . '/assets/css/main.css', array('group' => CSS_DEFAULT));
  drupal_add_js($module_path . '/assets/js/jquery_custom.js', array('group' => JS_DEFAULT));
  drupal_add_js($module_path . '/assets/js/jquery_ui_custom.js', array('group' => JS_DEFAULT));
  drupal_add_js($module_path . '/assets/js/jquery.reveal.modified.js', array('group' => JS_DEFAULT));
  drupal_add_js($module_path . '/assets/js/main.js', array('group' => JS_DEFAULT));
  print(drupal_render(drupal_get_form('advanced_settings_form')));
  ShareaholicAdmin::draw_modal_popup();
?>