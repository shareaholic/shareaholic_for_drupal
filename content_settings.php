<?php
/**
 * File for the ShareaholicContentSettings class.
 *
 * @package shareaholic
 */

/**
 * An interface to the Shareaholic Content Settings database table
 *
 * @package shareaholic
 */
class ShareaholicContentSettings {

  public static function schema() {
    $schema['shareaholic_content_settings'] = array(
      'description' => 'Stores shareaholic specific settings for nodes.',
      'fields' => array(
        'nid' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => 'The {node}.nid to store settings.',
        ),
        'settings' => array(
          'type' => 'text',
          'size' => 'medium',
          'not null' => TRUE,
          'serialize' => TRUE,
          'description' => 'The settings object for a node',
        ),
      ),
      'primary key' => array('nid'),
      'foreign keys' => array(
        'dnv_node' => array(
          'table' => 'node',
          'columns' => array('nid' => 'nid'),
        ),
      ),
    );
    return $schema;
  }

}