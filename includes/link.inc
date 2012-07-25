<?php
/**
 * @file
 * Link module integration.
 */

/**
 * Implements hook_shareaholic_field_formatter_info() on behalf of link.module.
 */
function link_shareaholic_field_formatter_info() {
  return array(
    'link_field' => array(
      'callback' => 'link_shareaholic_field_formatter_data'
    ),
  );
}

/**
 * Callback for Link module Shareaholic formatter.
 */
function link_shareaholic_field_formatter_data($item) {
  return array(
    'url' => $item['url'],
    'title' => $item['title'],
  );
}
