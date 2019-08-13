<?php

namespace Drupal\shareaholic\Form;

use Drupal\Core\Config\Config;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\shareaholic\Helper\ShareaholicEntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ResetForm.
 */
class ResetForm extends FormBase {

  /** @var ShareaholicEntityManager */
  private $shareaholicEntityManager;

  /** @var Config */
  private $shareaholicConfig;

  public function __construct(ShareaholicEntityManager $shareaholicEntityManager, Config $config)
  {
    $this->shareaholicEntityManager = $shareaholicEntityManager;
    $this->shareaholicConfig = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('shareaholic.entity_manager'),
      $container->get('shareaholic.editable_config')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'shareaholic_reset_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => Markup::create($this->t("Are you sure you want to reset your Shareaholic settings? That will make you lose all your configuration and will clear all cache.")),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Reset'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $nodeTypes = $this->shareaholicEntityManager->getShareaholicEnabledNodeTypes();

    foreach ($nodeTypes as $nodeType) {
      $this->shareaholicEntityManager->disableShareaholic($nodeType);
    }

    // TODO: Use shareaholic.settings.yml .
    $this->shareaholicConfig->set('api_key', NULL)
                            ->set('verification_key', NULL)
                            ->set('enable_og_tags', TRUE);

    $this->shareaholicConfig->save();

    // TODO Make cache clearing more targeted.
    drupal_flush_all_caches();

    $this->messenger()->addMessage($this->t("Reset has been successful. Cache clear has been succesfull."));
    $form_state->setRedirect('shareaholic.settings.advanced');
  }
}
