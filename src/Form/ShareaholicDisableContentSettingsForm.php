<?php

namespace Drupal\shareaholic\Form;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\node\NodeTypeInterface;
use Drupal\shareaholic\Api\EventLogger;
use Drupal\shareaholic\Helper\ShareaholicEntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ShareaholicDisableContentSettingsForm.
 */
class ShareaholicDisableContentSettingsForm extends FormBase {

  /** @var ConfigEntityStorageInterface */
  private $nodeTypeStorage;

  /** @var ShareaholicEntityManager */
  private $shareaholicEntityManager;

  /** @var EventLogger */
  private $eventLogger;

  public function __construct(ConfigEntityStorageInterface $nodeTypeStorage, ShareaholicEntityManager $shareaholicEntityManager, EventLogger $eventLogger)
  {
    $this->nodeTypeStorage = $nodeTypeStorage;
    $this->shareaholicEntityManager = $shareaholicEntityManager;
    $this->eventLogger = $eventLogger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('entity_type.manager')->getStorage('node_type'),
      $container->get('shareaholic.entity_manager'),
      $container->get('shareaholic.api.event_logger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'shareaholic_disable_content_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nodeType = NULL) {

    /** @var NodeTypeInterface $nodeType */
    $nodeType = $this->nodeTypeStorage->load($nodeType);

    if (!$nodeType) {
      $form['message'] = [
        '#type' => 'markup',
        '#markup' => Markup::create($this->t('There is no such node type.')),
      ];
      return $form;
    }

    if (!$this->shareaholicEntityManager->isShareaholicEnabled($nodeType)) {
      $form['message'] = [
        '#type' => 'markup',
        '#markup' => Markup::create($this->t('This node type is not Shareaholic enabled.')),
      ];
      return $form;
    }

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => Markup::create($this->t("Are you sure you want disable Shareaholic on the '@bundle' node type? You will lose all your node-specific Shareaholic settings.", ['@bundle' => $nodeType->id()])),
    ];

    $form['type'] = [
      '#type' => 'value',
      '#value' => $nodeType->id(),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Disable'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    /** @var NodeTypeInterface $nodeType */
    $nodeType = $this->nodeTypeStorage->load($form_state->getValue('type'));

    if (!$nodeType || !$this->shareaholicEntityManager->isShareaholicEnabled($nodeType)) {
      return;
    }

    $this->shareaholicEntityManager->disableShareaholic($nodeType);
    $this->eventLogger->log($this->eventLogger::EVENT_UPDATED_SETTINGS);

    $this->messenger()->addMessage($this->t("Content type '@type' is now Shareaholic disabled.", ['@type' => $nodeType->id()]));
    $form_state->setRedirect('shareaholic.settings.content');
  }
}
