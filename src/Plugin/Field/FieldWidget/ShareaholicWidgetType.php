<?php

namespace Drupal\shareaholic\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'shareaholic_widget' widget.
 *
 * @FieldWidget(
 *   id = "shareaholic_widget_type",
 *   label = @Translation("Shareaholic widget type"),
 *   field_types = {
 *     "shareaholic_content_settings"
 *   }
 * )
 */
class ShareaholicWidgetType extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['shareaholic'] = [
      '#type' => 'details',
      '#title' => $this->t('Shareoholic content settings'),
      '#open' => FALSE,

      'hide_recommendations' => [
        '#type' => 'checkbox',
        '#title' => $this->t("Hide Shareaholic Related Content widgets on this content's page."),
        '#default_value' => $items[$delta]->hide_recommendations ?? FALSE,
      ],
      'hide_share_buttons' => [
        '#type' => 'checkbox',
        '#title' => $this->t("Hide Shareaholic Share Buttons on this content's page."),
        '#default_value' => $items[$delta]->hide_share_buttons ?? FALSE,
      ],
      'exclude_from_recommendations' => [
        '#type' => 'checkbox',
        '#title' => $this->t("Exclude this content from Related Content."),
        '#default_value' => $items[$delta]->exclude_from_recommendations ?? FALSE,
      ],
      'exclude_og_tags' => [
        '#type' => 'checkbox',
        '#title' => $this->t("Don't add Open Graph tags to this content."),
        '#default_value' => $items[$delta]->exclude_og_tags ?? FALSE,
      ],
    ];

    return $element;
  }

  /**
   * {@inheritDoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state)
  {
    foreach ($values as &$value) {
      $shareaholic = $value['shareaholic'];
      unset($value['shareaholic']);

      foreach ($shareaholic as $propertyName => $propertyValue) {
        $value[$propertyName] = $propertyValue;
      }
    }

    return $values;
  }
}
