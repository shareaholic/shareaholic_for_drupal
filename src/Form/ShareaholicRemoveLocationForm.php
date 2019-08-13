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
 * Class ShareaholicRemoveLocationForm.
 */
class ShareaholicRemoveLocationForm extends FormBase {

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
    return 'shareaholic_remove_location_form';
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
  public function buildForm(array $form, FormStateInterface $form_state, $nodeType = NULL, $locationType = NULL, $location = NULL) {

    /** @var NodeTypeInterface $nodeType */
    $nodeType = $this->nodeTypeStorage->load($nodeType);

    if (!$nodeType) {
      $form['message'] = [
        '#type' => 'markup',
        '#markup' => Markup::create($this->t('There is no such node type.')),
      ];
      return $form;
    }

    if (empty($locationType) || !in_array($locationType, $this->getLocationTypes(), TRUE)) {
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
      '#markup' => Markup::create($this->t("Are you sure you want to remove location '@locationId' of the type '@locationType' from the content type '@nodeType'?", ['@locationType' => $locationType, '@nodeType' => $nodeType->id(), '@locationId' => $location])),
    ];

    $form['location'] = [
      '#type' => 'value',
      '#value' => $location,
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
      '#value' => $this->t('Remove'),
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

    if (!in_array($locationType, $this->getLocationTypes(), TRUE)) {
      $form_state->setErrorByName('locationType', $this->t("Unknown location type!"));
    }

    if (!empty($form_state->getErrors())) {
      return;
    }

    $locations = $nodeType->getThirdPartySetting('shareaholic', "locations_$locationType", []);

    if (!in_array($values['location'], $locations, TRUE)) {
      $form_state->setErrorByName('location', $this->t('There is no such location!'));
    }

    if ($values['location'] === 'default') {
      $form_state->setErrorByName('location', $this->t('Default location is not removable!'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    /** @var NodeTypeInterface $nodeType */
    $nodeType = $this->nodeTypeStorage->load($form_state->getValue('nodeType'));

    if (!$nodeType || !$this->shareaholicEntityManager->isShareaholicEnabled($nodeType)) {
      return;
    }

    $locationType = $form_state->getValue('locationType');

    $locations = $nodeType->getThirdPartySetting('shareaholic', "locations_$locationType", []);
    $location = $form_state->getValue('location');

    if (($key = array_search($location, $locations, TRUE)) !== FALSE) {
        unset($locations[$key]);
    }

    $nodeType->setThirdPartySetting('shareaholic', "locations_$locationType", $locations);
    $nodeType->save();
    $this->messenger()->addMessage($this->t("Location '@locationId' type '@locationType' has been added to the node type '@nodeType'!", ['@locationType' => $locationType, '@nodeType' => $nodeType->id(), '@locationId' => $location]));
    $form_state->setRedirect('shareaholic.settings.content');
  }

  private function getLocationTypes(): array {
    return ['share_buttons', 'recommendations'];
  }
}
