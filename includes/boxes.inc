<?php
/**
 * @file
 * Boxes module integration.
 */

/**
 * Implementation of hook_default_box()
 */
function shareaholic_default_box() {
  $boxs = array();

  $box = new stdClass;
  $box->disabled = FALSE; /* Edit this to true to make a default box disabled initially */
  $box->api_version = 1;
  $box->delta = 'example_shareaholic';
  $box->plugin_key = 'simple';
  $box->title = 'Example: Shareaholic';
  $box->description = 'Example: Shareaholic';
  $box->options = array(
    'body' => '<?php
$profile = \'default\';
$url = url(\'<front>\', array(\'absolute\' => TRUE));
$title = variable_get(\'site_name\', \'Drupal\');

print theme(\'shareaholic\', array(
  \'profile\' => $profile,
  \'url\' => $url,
  \'title\' => $title,
  \'id\' => \'block-shareaholic-example\',
));
?>',
    'format' => '3',
  );
  $boxs['example_shareaholic'] = $box;

  return $boxs;
}
