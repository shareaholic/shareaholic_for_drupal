<?php
/**
 * @file
 * Node Reference module integration.
 */

/**
 * Implements hook_shareaholic_field_formatter_info() on behalf of node_reference.module.
 */
function node_reference_shareaholic_field_formatter_info() {
  return array(
    'node_reference' => array(
      'callback' => 'node_reference_shareaholic_field_formatter_data'
    ),
  );
}

/**
 * Callback for Link module Shareaholic formatter.
 */
function node_reference_shareaholic_field_formatter_data($item) {
  $node = node_load($item['nid']);

  return array(
    'url' => url("node/{$node->nid}", array('absolute' => TRUE)),
    'title' => $node->title,
  );
}
