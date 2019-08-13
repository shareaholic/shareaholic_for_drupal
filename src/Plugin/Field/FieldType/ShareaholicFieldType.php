<?php

namespace Drupal\shareaholic\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'shareaholic_content_settings' field type.
 *
 * @FieldType(
 *   id = "shareaholic_content_settings",
 *   label = @Translation("Shareaholic content settings"),
 *   description = @Translation("Per content-unit settings used by shareaholic widgets and other features."),
 *   default_widget = "shareaholic_widget_type",
 *   default_formatter = "shareaholic_formatter_type",
 *   no_ui = true
 * )
 */
class ShareaholicFieldType extends FieldItemBase {


  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    $properties = [];

    foreach (static::getListOfSettings() as $setting) {
      // Prevent early t() calls by using the TranslatableMarkup.
      $properties[$setting] = DataDefinition::create('boolean')
        ->setLabel(new TranslatableMarkup('Boolean value'))
        ->setRequired(TRUE);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {

    $columns = [];

    foreach (static::getListOfSettings() as $setting) {
      $columns[$setting] = [
        'type' => 'int',
        'not null' => TRUE,
        'default' => 0,
      ];
    }

    return [
      'columns' => $columns,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $values = [];

    foreach (static::getListOfSettings() as $setting) {
      $values[$setting] = (bool) mt_rand(0, 1);
    }

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $isEmpty = TRUE;
    foreach (static::getListOfSettings() as $setting) {
      if ((bool) $this->get($setting)->getValue()) $isEmpty = FALSE;
    }
    return $isEmpty;
  }

  public static function getListOfSettings() {
    return [
      'hide_recommendations',
      'hide_share_buttons',
      'exclude_from_recommendations',
      'exclude_og_tags',
    ];
  }
}
