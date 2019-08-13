<?php

namespace Drupal\shareaholic\Form;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\NodeTypeInterface;
use Drupal\shareaholic\Helper\ShareaholicEntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ShareaholicEnableContentSettingsForm.
 */
class ShareaholicEnableContentSettingsForm extends FormBase {

  /** @var ConfigEntityStorageInterface */
  private $nodeTypeStorage;

  /** @var ShareaholicEntityManager */
  private $shareaholicEntityManager;

  public function __construct(ConfigEntityStorageInterface $nodeTypeStorage, ShareaholicEntityManager $shareaholicEntityManager)
  {
    $this->nodeTypeStorage = $nodeTypeStorage;
    $this->shareaholicEntityManager = $shareaholicEntityManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('entity_type.manager')->getStorage('node_type'),
      $container->get('shareaholic.entity_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'shareaholic_enable_content_settings_form';
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

    if ($this->shareaholicEntityManager->isShareaholicEnabled($nodeType)) {
      $form['message'] = [
        '#type' => 'markup',
        '#markup' => Markup::create($this->t('This node type is already Shareaholic enabled.')),
      ];
      return $form;
    }

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => Markup::create($this->t("Are you sure you want to enable Shareaholic on the '@bundle' node type?", ['@bundle' => $nodeType->id()])),
    ];

    $form['type'] = [
      '#type' => 'value',
      '#value' => $nodeType->id(),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Enable'),
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

    if (!$nodeType || $this->shareaholicEntityManager->isShareaholicEnabled($nodeType)) {
      return;
    }

    $field = FieldConfig::loadByName('node', $nodeType->id(), 'shareaholic');

    if (!empty($field)) {
      return;
    }

    $this->shareaholicEntityManager->enableShareaholic($nodeType);

    $this->messenger()->addMessage($this->t("Content type '@type' is now Shareaholic enabled!", ['@type' => $nodeType->id()]));
    $form_state->setRedirect('shareaholic.settings.content');
  }
}
