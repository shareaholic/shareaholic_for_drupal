<?php
/**
 * @file
 * Shareaholic module integration.
 */

/**
 * Implementation of hook_default_shareaholic_profile().
 */
function shareaholic_default_shareaholic_profile() {
  $export = array();

  $profile = new stdClass;
  $profile->disabled = FALSE; /* Edit this to true to make a default profile disabled initially */
  $profile->api_version = 2;
  $profile->name = 'default';
  $profile->config = array(
    'service' => '5,7,88,2,3,74,52,191,236,54',
    'fbLikeButton' => '0',
    'fbButtonPos' => 'bottom-right',
    'dontShowShareCount' => '0',
    'designer_toolTips' => '1',
    'tip_bg_color' => '#000000',
    'tip_text_color' => '#FFFFFF',
    'rel' => 'nofollow',
    'target' => '_blank',
    'shrlink' => '1',
    'shortener' => 'google',
    'twitter_template' => '${title} - ${short_link} via @Shareaholic',
    'expand' => 1,
    'center' => TRUE,
    'spaced' => FALSE,
    'perfOptionSetting' => '1', 
    'changethis' => '1',  
    'classic_options'=>'32'  
  );
  $export['default'] = $profile;

  return $export;
}
