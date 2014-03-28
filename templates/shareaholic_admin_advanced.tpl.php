<?php ShareaholicAdmin::include_css_js_assets(); ?>
<div id="shareaholic-form-container">
  <ul class="nav nav-tabs">
    <li><?php print l(t('App Manager'), 'admin/config/shareaholic/settings'); ?></li>
    <li class="active"><?php print l(t('Advanced Settings'), 'admin/config/shareaholic/advanced'); ?></li>
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
    ?>

    <fieldset class="app">
      <legend><h2><?php print t('Server Connectivity'); ?></h2></legend>
      <?php if (ShareaholicUtilities::connectivity_check() == 'SUCCESS') { ?>
        <span class="key-status passed"><?php  print t('All Shareaholic servers are reachable'); ?></span>
        <div class="key-description"><?php print t('Shareaholic should be working correctly.'); ?> <?php print t('All Shareaholic servers are accessible.'); ?></div>
      <?php } else { // can't connect to any server ?>
        <span class="key-status failed"><?php print t('Unable to reach any Shareaholic server'); ?></span> <a href="#" onClick="window.location.reload(); this.innerHTML='<?php print t('Checking...'); ?>';"><?php print t('Re-check'); ?></a>
        <div class="key-description"><?php echo sprintf( t('A network problem or firewall is blocking all connections from your web server to Shareaholic.com.  <strong>Shareaholic cannot work correctly until this is fixed.</strong>  Please contact your web host or firewall administrator and give them <a href="%s" target="_blank">this information about Shareaholic and firewalls</a>. Let us <a href="#" onclick="%s">know</a> too, so we can follow up!'), 'http://blog.shareaholic.com/shareaholic-hosting-faq/', 'SnapEngage.startLink();','</a>'); ?></div>
      <?php } ?>
    </fieldset>

    <?php
      $form = drupal_get_form('shareaholic_reset_plugin_form');
      print(drupal_render($form));
      ShareaholicAdmin::draw_modal_popup();
      print '</div>';
    ?>
  </div>
</div>

<?php ShareaholicAdmin::include_snapengage(); ?>