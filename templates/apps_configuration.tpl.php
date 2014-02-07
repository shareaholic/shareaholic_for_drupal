<div id='app_settings'>

  <div class='clear'></div>

  <fieldset class="app">
    <legend><h2><img src="<?php echo '/' . SHAREAHOLIC_ASSET_DIR; ?>/img/related_content@2x.png" height=32 width=32 /> <?php print t('Related Content / Recommendations'); ?></h2></legend>

  <span class="helper"><i class="icon-star"></i> <?php print t('Pick where you want Related Content to be displayed. Click "Customize" to customize look & feel, themes, block lists, etc.'); ?></span>

  <div class='clear'></div>

  <strong><?php print t('Related Content:'); ?></strong>

  <?php
    $status = ShareaholicUtilities::recommendations_status_check();
    if ($status == 'processing' || $status == 'unknown'){
      echo '<img class="shrsb_health_icon" align="top" src="/' . SHAREAHOLIC_ASSET_DIR . '/img/circle_yellow.png" />' . t('Processing');
    } else {
      echo '<img class="shrsb_health_icon" align="top" src="/' . SHAREAHOLIC_ASSET_DIR . '/img/circle_green.png" />' . t('Ready');
    }
  ?>

  </fieldset>
</div>

<div class='clear'></div>

<div class="row" style="padding-top:20px; padding-bottom:35px;">
  <div class="span2">
    <?php print $variables['apps_configuration']['hidden'] ?>
    <?php print $variables['apps_configuration']['submit'] ?>
  </div>
</div>
