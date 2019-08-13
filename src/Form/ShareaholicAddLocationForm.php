<?php

namespace Drupal\shareaholic\Form;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\node\NodeTypeInterface;
use Drupal\shareaholic\Helper\ShareaholicEntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ShareaholicAddLocationForm.
 */
class ShareaholicAddLocationForm extends FormBase {

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
    return 'shareaholic_add_location_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nodeType = NULL, $locationType = NULL) {

    /** @var NodeTypeInterface $nodeType */
    $nodeType = $this->nodeTypeStorage->load($nodeType);

    if (!$nodeType) {
      $form['message'] = [
        '#type' => 'markup',
        '#markup' => Markup::create($this->t('There is no such node type.')),
      ];
      return $form;
    }

    if (empty($locationType) || !in_array($locationType, ContentSettingsForm::getLocationTypes(), TRUE)) {
      $form['message'] = [
        '#type' => 'markup',
        '#markup' => Markup::create($this->t('There is no such location type.')),
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
      '#markup' => Markup::create($this->t("Are you sure you want to add a new location of the type '@locationType' to the content type '@nodeType'?", ['@locationType' => $locationType, '@nodeType' => $nodeType->id()])),
    ];

    $form['location'] = [
      '#type' => 'machine_name',
      '#title' => $this->t("Location's name"),
      '#required' => TRUE,
      '#machine_name' => [
        'exists' => static function(){return FALSE;},
      ],
    ];

    $form['nodeType'] = [
      '#type' => 'value',
      '#value' => $nodeType->id(),
    ];

    $form['locationType'] = [
      '#type' => 'value',
      '#value' => $locationType,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $form_state->setValue('location', strtolower($form_state->getValue('location')));

    $values = $form_state->getValues();

    /** @var NodeTypeInterface $nodeType */
    $nodeType = $this->nodeTypeStorage->load($form_state->getValue('nodeType'));

    if (!$nodeType || !$this->shareaholicEntityManager->isShareaholicEnabled($nodeType)) {
      $form_state->setErrorByName('nodeType', $this->t("Node type doesn't exist or is not Shareaholic enabled!"));
    }

    $locationType = $form_state->getValue('locationType');

    if (!in_array($locationType, ContentSettingsForm::getLocationTypes(), TRUE)) {
      $form_state->setErrorByName('locationType', $this->t("Unknown location type!"));
    }

    if (!empty($form_state->getErrors())) {
      return;
    }


    $locations = $nodeType->getThirdPartySetting('shareaholic', "locations_$locationType", []);
    if (in_array($values['location'], $locations, TRUE)) {
      $form_state->setErrorByName('location', $this->t('Locations have to be unique!'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    /** @var NodeTypeInterface $nodeType */
    $nodeType = $this->nodeTypeStorage->load($form_state->getValue('nodeType'));

    $locationType = $form_state->getValue('locationType');
    $locationId = $form_state->getValue('location');

    $this->shareaholicEntityManager->addLocation($locationId, $locationType, $nodeType);

    $this->messenger()->addMessage($this->t("Location type '@locationType' of id '@locationId' has been added to the node type '@nodeType'!", ['@locationType' => $locationType, '@nodeType' => $nodeType->id(), '@locationId' => $locationId]));
    $form_state->setRedirect('shareaholic.settings.content');
  }
}
