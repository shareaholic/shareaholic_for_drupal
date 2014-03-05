<?php ShareaholicAdmin::include_css_js_assets(); ?>
<div id="shareaholic-form-container">
  <ul class="nav nav-tabs">
    <li><?php print l(t('App Manager'), '/admin/config/shareaholic/settings'); ?></li>
    <li class="active"><?php print l(t('Advanced Settings'), '/admin/config/shareaholic/advanced'); ?></li>
  </ul>

  <div style="margin-top:20px;"></div>
  <div class='unit size4of5 wrap' style="min-height:300px;">
  <span class="helper">
    <i class="icon-star"></i>
    <?php print t('You rarely should need to edit the settings on this page.'); ?>
    <?php print t('After changing any Shareaholic advanced setting, it is good practice to clear your cache.'); ?>
  </span>
    <?php
      $form = drupal_get_form('shareaholic_advanced_settings_form');
      print(drupal_render($form));
      $form = drupal_get_form('shareaholic_reset_plugin_form');
      print(drupal_render($form));
      ShareaholicAdmin::draw_modal_popup();
      print '</div>';
    ?>
  </div>
</div>