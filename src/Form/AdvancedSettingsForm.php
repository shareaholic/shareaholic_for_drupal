<?php

namespace Drupal\shareaholic\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\shareaholic\Api\ShareaholicApi;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AdvancedSettingsForm.
 */
class AdvancedSettingsForm extends ConfigFormBase {

  /** @var @var ShareaholicApi */
  private $shareaholicApi;

  /** @var CacheBackendInterface */
  private $renderCache;

  /** @var Config */
  private $shareaholicConfig;

  public function __construct(CacheBackendInterface $renderCache, ConfigFactoryInterface $config_factory, ShareaholicApi $shareaholicApi, Config $config)
  {
    parent::__construct($config_factory);
    $this->shareaholicApi = $shareaholicApi;
    $this->renderCache = $renderCache;
    $this->shareaholicConfig = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cache.render'),
      $container->get('config.factory'),
      $container->get('shareaholic.api'),
      $container->get('shareaholic.editable_config')
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

    $form['advanced']['disable_og_tags'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable Open Graph tags (it is recommended NOT to disable open graph tags)'),
      '#description' => $this->t('Changing this option will result in render cache clearance, to update all node pages.'),
      '#weight' => '0',
      '#default_value' => $this->shareaholicConfig->get('disable_og_tags')
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
      '#type' => 'button',
      '#value' => $this->t('Reset'),
    ];

    return parent::buildForm($form, $form_state);
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

    $disOGTagsOldValue = $this->shareaholicConfig->get('disable_og_tags');
    $disOGTagsNewValue = $form_state->getValue('disable_og_tags');

    $this->shareaholicConfig
          ->set('disable_og_tags', $disOGTagsNewValue)
          ->save();

    if ($disOGTagsOldValue !== $disOGTagsNewValue) {
      $this->renderCache->invalidateAll();
      $this->messenger()->addMessage('Render cache has been cleared');
    }

    // Clear render cache so the changes to the og tags will disappear from all pages.
    // TODO it should run only if disable_og_tags has changed.
    parent::submitForm($form, $form_state);
  }
}
