<?php ShareaholicAdmin::include_css_js_assets(); ?>
<div id="shareaholic-form-container">
<div class='wrap'>

<div class='reveal-modal' id='editing_modal'>
  <div id='iframe_container' class='bg-loading-img' allowtransparency='true'></div>
  <a class="close-reveal-modal">&#215;</a>
</div>

<script>
window.first_part_of_url = "<?php echo ShareaholicUtilities::URL . '/publisher_tools/' . ShareaholicUtilities::get_option('api_key')?>/";
window.verification_key = "<?php echo ShareaholicUtilities::get_option('verification_key') ?>";
</script>

<div class='unit size3of5'>
<?php print(drupal_render(drupal_get_form('shareaholic_apps_configuration_form'))); ?>
</div>
<?php /* ShareaholicUtilities::load_template('why_to_sign_up', array('url' => Shareaholic::URL)) */ ?>
</div>


<?php ShareaholicAdmin::draw_modal_popup(); ?>
</div>