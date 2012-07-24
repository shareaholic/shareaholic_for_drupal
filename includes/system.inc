<?php
/**
 * @file
 * System module integration.
 */

/**
 * Implements hook_theme().
 */
function shareaholic_form_alter(&$form, &$form_state, $form_id) {
  drupal_alter('shareaholic_form_alter', $form, $form_state, $form_id);
}
