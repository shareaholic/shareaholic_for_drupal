<?php
/**
 * @file
 * System module integration.
 */

/**
 * Implements hook_theme().
 */
function sexybookmarks_form_alter(&$form, &$form_state, $form_id) {
  drupal_alter('sexybookmarks_form_alter', $form, $form_state, $form_id);
}
