<div id='app_settings'>

  <div class='clear'></div>

  <fieldset class="app">
    <legend><h2><img src="<?php echo '/' . SHAREAHOLIC_ASSET_DIR; ?>/img/related_content@2x.png" height=32 width=32 /> <?php print t('Related Content / Recommendations'); ?></h2></legend>

    <span class="helper"><i class="icon-star"></i> <?php print t('Pick where you want Related Content to be displayed. Click "Customize" to customize look & feel, themes, block lists, etc.'); ?></span>
    <?php $page_types = node_type_get_types(); ?>
    <?php $settings = ShareaholicUtilities::get_settings(); ?>
    <?php foreach($page_types as $key => $page_type) { ?>
      <?php if (isset($settings['location_name_ids']['recommendations']["{$page_type->type}_below_content"])) { ?>
        <?php $location_id = $settings['location_name_ids']['recommendations']["{$page_type->type}_below_content"] ?>
      <?php } else { $location_id = ''; } ?>
      <fieldset id='recommendations'>
        <legend><?php echo ucwords($page_type->name) ?></legend>
          <div>
            <input type="checkbox" id="recommendations_<?php echo "{$page_type->type}_below_content" ?>" name="recommendations[<?php echo "{$page_type->type}_below_content" ?>]" class="check"
            <?php if (isset($settings['recommendations']["{$page_type->type}_below_content"])) { ?>
              <?php echo ($settings['recommendations']["{$page_type->type}_below_content"] == 'on' ? 'checked' : '') ?>
            <?php } ?>>
            <label for="recommendations_<?php echo "{$page_type->type}_below_content" ?>">Below Content</label>
            <button data-app='recommendations'
                    data-location_id='<?php echo $location_id ?>'
                    data-href="recommendations/locations/{{id}}/edit"
                    class="btn btn-success">
            <?php print t('Customize'); ?></button>
          </div>
      </fieldset>
    <?php } ?>

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
    <?php print $variables['shareaholic_apps_configuration']['hidden'] ?>
    <?php print $variables['shareaholic_apps_configuration']['submit'] ?>
  </div>
</div>
