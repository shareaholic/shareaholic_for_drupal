<?php ShareaholicAdmin::include_css_js_assets(); ?>
<div id="shareaholic-form-container">
<ul class="nav nav-tabs">
  <li class="active"><?php print l(t('App Manager'), 'admin/config/shareaholic/settings'); ?></li>
  <li><?php print l(t('Advanced Settings'), 'admin/config/shareaholic/advanced'); ?></li>
</ul>
<div class='wrap'>

<div class='reveal-modal' id='editing_modal'>
  <div id='iframe_container' class='bg-loading-img' allowtransparency='true'></div>
  <a class="close-reveal-modal">&#215;</a>
</div>

<script>
window.first_part_of_url = "<?php echo ShareaholicUtilities::URL . '/publisher_tools/' . ShareaholicUtilities::get_option('api_key')?>/";
window.verification_key = "<?php echo ShareaholicUtilities::get_option('verification_key') ?>";
window.shareaholic_api_key = "<?php echo ShareaholicUtilities::get_option('api_key'); ?>";
</script>

<div class='unit size3of5'>
<?php
  $form = drupal_get_form('shareaholic_apps_configuration_form');
  print(drupal_render($form));
?>
</div>

  <div class="signuppromo unit size1of5">
  <p class="promoh1"><?php print t('Unlock additional customization options when you connect this module to your FREE Shareaholic account.'); ?></p>
  <ul>
    <li><?php print t('Brand your social shares. For example, you can make all Twitter shares say "by @Twitterhandle"'); ?></li>
    <li><?php print t('Pick your favorite URL shortener, including support for branded bitly short links.'); ?></li>
    <li><?php print t('Additional themes for share buttons, related content, etc to match your site.'); ?></li>
    <li><?php print t('Opportunities to make money with your site, plus lots more!'); ?></li>
  </ul>
  <button data-href='edit' id='general_settings' class="btn btn-success btn-large"><?php print t('Edit General Website Settings'); ?></button>
  <p class="signuppromo_note"><?php print t("Connecting is simple. Simply click the button above and sign in to your Shareaholic account to connect this module to your account. If you don't have an account already, simply create a new account — it takes just seconds and it's free!"); ?></p>
  </div>

</div>


<?php ShareaholicAdmin::draw_modal_popup(); ?>
<?php ShareaholicAdmin::draw_verify_api_key(); ?>
<?php ShareaholicAdmin::show_footer(); ?>
<?php ShareaholicAdmin::include_snapengage(); ?>
</div>