<?php
  ShareaholicAdmin::include_css_js_assets();
  print '<div id="shareaholic-form-container">';
  print(drupal_render(drupal_get_form('shareaholic_advanced_settings_form')));
  print(drupal_render(drupal_get_form('shareaholic_reset_plugin_form')));
  ShareaholicAdmin::draw_modal_popup();
  print '</div>';
?>