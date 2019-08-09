<?php

namespace Drupal\shareaholic\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\shareaholic\Api\ShareaholicApi;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

  /** @var Client */
  private $httpClient;

  /** @var @var ShareaholicApi */
  private $shareaholicApi;

  public function __construct(Client $httpClient, ShareaholicApi $shareaholicApi)
  {
    $this->httpClient = $httpClient;
    $this->shareaholicApi = $shareaholicApi;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('shareaholic.api')
    );
  }

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

    $servers_check = $this->connectivityCheck();

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
      '#title' => $this->t('Check connection to Shareaholic Servers'),
      '#weight' => '0',
      '#default_value' => $servers_check ? $this->t('All Shareaholic servers are reachable') : $this->t('Unable to reach any Shareaholic server'),
      '#description' => $servers_check ? $this->t('Shareaholic should be working correctly. All Shareaholic servers are accessible.') : $this->t('A network problem or firewall is blocking all connections from your web server to Shareaholic.com.  <strong>Shareaholic cannot work correctly until this is fixed.</strong>  Please contact your web host or firewall administrator and give them <a href="http://blog.shareaholic.com/shareaholic-hosting-faq/" target="_blank">this information about Shareaholic and firewalls</a>. Let us <a href="#" onclick="%s">know</a> too, so we can follow up!'),
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

  /**
   * Server Connectivity check
   */
  private function connectivityCheck() {
    $health_check_url = $this->shareaholicApi::HEALTH_CHECK_URL;
    $response = $this->httpClient->get($health_check_url);
    return $response->getStatusCode() === 200;
  }
}
