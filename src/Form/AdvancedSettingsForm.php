<?php

namespace Drupal\shareaholic\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\shareaholic\Api\EventLogger;
use Drupal\shareaholic\Api\ShareaholicApi;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AdvancedSettingsForm.
 */
class AdvancedSettingsForm extends ConfigFormBase {

  /** @var @var ShareaholicApi */
  private $shareaholicApi;

  /** @var Config */
  private $shareaholicConfig;

  /** @var \Drupal\shareaholic\Api\EventLogger */
  private $eventLogger;

  public function __construct(ConfigFactoryInterface $config_factory, ShareaholicApi $shareaholicApi, Config $config, EventLogger $eventLogger)
  {
    parent::__construct($config_factory);
    $this->shareaholicApi = $shareaholicApi;
    $this->shareaholicConfig = $config;
    $this->eventLogger = $eventLogger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('shareaholic.api'),
      $container->get('shareaholic.editable_config'),
      $container->get('shareaholic.api.event_logger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'shareaholic_advanced_settings_form';
  }

  /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
      return [];
    }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['advanced'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Advanced'),
    ];

    $form['advanced']['enable_og_tags'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Open Graph tags (it is recommended for Open Graphs tags to be enabled)'),
      '#description' => $this->t('To see the effect of the change on node pages, cache will have to be cleared.'),
      '#weight' => '0',
      '#default_value' => $this->shareaholicConfig->get('enable_og_tags')
    ];

    $form['server'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Server Connectivity'),
    ];

    $servers_check = $this->shareaholicApi->connectivityCheck();

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
      '#default_value' => $this->shareaholicConfig->get('api_key'),
    ];

    $form['reset'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Reset'),
    ];

    $form['reset']['reset_button'] = [
      '#type' => 'link',
      '#title' => $this->t('Reset'),
      '#attributes' => [
        'class' => ['button', 'button--secondary'],
      ],
      '#url' => Url::fromRoute('shareaholic.settings.reset'),
    ];

    $form['#attached']['html_head'] = [shareaholic_get_chat_for_head()];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $disOGTagsNewValue = $form_state->getValue('enable_og_tags');

    $this->shareaholicConfig->set('enable_og_tags', $disOGTagsNewValue)->save();
    $this->eventLogger->log($this->eventLogger::EVENT_UPDATED_SETTINGS);

    parent::submitForm($form, $form_state);
  }
}
