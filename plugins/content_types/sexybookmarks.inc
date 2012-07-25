<?php
/**
 * @file
 * CTools Content Types plugin for Shareaholic.
 */

$plugin = array(
  'title' => t('Shareaholic'),
  'description' => t('Show the Shareaholic widget.'),
  'required context' => new ctools_context_required(t('Node'), 'node'),
  'category' => t('Shareaholic'),
  'defaults' => array('profile' => 'default'),
);

/**
 * Render callback.
 */
function shareaholic_shareaholic_content_type_render($subtype, $conf, $args, $context) {
  if (isset($context->data) && !empty($conf['profile'])) {
    $node = clone($context->data);

    $block = new stdClass();
    $block->module = 'shareaholic';
    $block->delta = $node->nid;
    $block->title = t('Shareaholic');
    $block->content = theme('shareaholic', array(
      'profile' => $conf['profile'],
      'url' => url("node/{$node->nid}", array('absolute' => TRUE)),
      'title' => $node->title,
    ));

    return $block;
  }
  return;
}

/**
 * Form callback.
 */
function shareaholic_shareaholic_content_type_edit_form($form, &$form_state) {
  $options = array('' => '- None -');
  ctools_include('export');
  foreach (ctools_export_crud_load_all('shareaholic_profiles') as $profile) {
    if (empty($profile->disabled)) {
      $options[$profile->name] = $profile->name;
    }
  }
  $form['profile'] = array(
    '#type' => 'select',
    '#title' => t('Shareaholic profile'),
    '#options' => $options,
    '#default_value' => isset($form_state['conf']['profile']) ? $form_state['conf']['profile'] : '',
  );

  return $form;
}

function shareaholic_shareaholic_content_type_edit_form_submit($form, &$form_state) {
  // Copy everything from our defaults.
  foreach (array_keys($form_state['plugin']['defaults']) as $key) {
    $form_state['conf'][$key] = $form_state['values'][$key];
  }
}
