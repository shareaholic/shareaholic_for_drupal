<?php

namespace Drupal\shareaholic\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'shareaholic_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "shareaholic_formatter_type",
 *   label = @Translation("Hidden"),
 *   field_types = {
 *     "shareaholic_content_settings"
 *   }
 * )
 */
class ShareaholicFormatterType extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = ['#markup' => $this->t('This field is not supposed to be displayed.')];
    }

    return $elements;
  }
}
