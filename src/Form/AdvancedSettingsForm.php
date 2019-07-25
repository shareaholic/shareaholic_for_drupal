<?php

namespace Drupal\shareaholic\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AdvancedSettingsForm.
 */
class AdvancedSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'advanced_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['disable_open_graph'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable Open Graph tags (it is recommended NOT to disable open graph tags)'),
      '#weight' => '0',
    ];
    $form['disable_share_counts'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable server-side Share Counts API (This feature uses server resources. When &quot;enabled&quot; share counts will appear for additional social networks.)'),
      '#weight' => '0',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      \Drupal::messenger()
        ->addMessage($key . ': ' . ($key === 'text_format' ? $value['value'] : $value));
    }
  }

}
