<?php ShareaholicAdmin::include_css_js_assets(); ?>
<div id="shareaholic-form-container">
<div class='wrap'>

<div class='reveal-modal' id='editing_modal'>
  <div id='iframe_container' class='bg-loading-img' allowtransparency='true'></div>
  <a class="close-reveal-modal">&#215;</a>
</div>

<div class='unit size3of5'>
  <form name="settings" method="post" action="">
  <input type="hidden" name="already_submitted" value="Y">

  <div id='app_settings'>

  <div class='clear'></div>

  <fieldset class="app"><legend><h2><img src="<?php echo '/' . SHAREAHOLIC_ASSET_DIR; ?>/img/related_content@2x.png" height=32 width=32 /> <?php print t('Related Content / Recommendations'); ?></h2></legend>
  <span class="helper"><i class="icon-star"></i> <?php print t('Pick where you want Related Content to be displayed. Click "Customize" to customize look & feel, themes, block lists, etc.'); ?></span>

    <div class='clear'></div>

    <strong><?php print t('Related Content:'); ?></strong>
    <?php
      /*
	    $status = ShareaholicUtilities::recommendations_status_check();
	    if ($status == "processing" || $status == 'unknown'){
	      echo '<img class="shrsb_health_icon" align="top" src="'.SHAREAHOLIC_ASSET_DIR.'img/circle_yellow.png" />'. sprintf(__('Processing', 'shareaholic'));
	    } else {
	      echo '<img class="shrsb_health_icon" align="top" src="'.SHAREAHOLIC_ASSET_DIR.'img/circle_green.png" />'. sprintf(__('Ready', 'shareaholic'));
	    }
	    */
	  ?>

  </fieldset>
  </div>

  <div class='clear'></div>
  <div class="row" style="padding-top:20px; padding-bottom:35px;">
    <div class="span2"><input type='submit' onclick="this.value='<?php print t('Saving Changes...'); ?>';" value='<?php print t('Save Changes'); ?>'></div>
  </div>
  </form>
</div>
<?php /* ShareaholicUtilities::load_template('why_to_sign_up', array('url' => Shareaholic::URL)) */ ?>
</div>


<?php /* ShareaholicAdmin::show_footer(); */ ?>
<?php /* ShareaholicAdmin::include_snapengage(); */ ?>
<?php ShareaholicAdmin::draw_modal_popup(); ?>
</div>