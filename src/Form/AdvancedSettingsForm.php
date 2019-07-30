<?php

namespace Drupal\shareaholic\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\shareaholic\Controller\UtilitiesController;

/**
 * Class AdvancedSettingsForm.
 */
class AdvancedSettingsForm extends FormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'shareaholic.settings';

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

    $config = $this->config(static::SETTINGS);

    $a = UtilitiesController::connectivity_check();


    $form['advanced'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Advanced'),
    ];

    $form['advanced']['disable_ogz_tags'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable Open Graph tags (it is recommended NOT to disable open graph tags)'),
      '#weight' => '0',
    ];

    $form['advanced']['disable_internal_share_counts_api'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable server-side Share Counts API (This feature uses server resources. When &quot;enabled&quot; share counts will appear for additional social networks.)'),
      '#weight' => '0',
    ];

    $form['server'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Server Connectivity'),
    ];

    $form['server']['shareaholic_servers'] = [
      '#type' => 'textfield',
      '#disabled' => TRUE,
      '#title' => $this->t('Disable server-side Share Counts API (This feature uses server resources. When &quot;enabled&quot; share counts will appear for additional social networks.)'),
      '#weight' => '0',
    ];

    $form['server']['sharecount_servers'] = [
      '#type' => 'textfield',
      '#disabled' => TRUE,
      '#title' => $this->t('The server-side Share Counts API should be working correctly. All servers and services needed by the API are accessible.)'),
      '#weight' => '0',
    ];

    $form['site_id'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Your Shareaholic Site ID'),
    ];

    $form['site_id']['shareaholic_id'] = [
      '#type' => 'textfield',
      '#disabled' => TRUE,
      '#weight' => '0',
      '#default_value' => $config->get('api_key'),
    ];

    $form['reset'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Reset'),
    ];

    $form['reset']['reset_button'] = [
      '#type' => 'button',
      '#value' => $this->t('Reset'),
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
