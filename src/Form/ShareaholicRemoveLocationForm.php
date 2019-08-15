<?php

namespace Drupal\shareaholic\Form;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Markup;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\NodeTypeInterface;
use Drupal\shareaholic\Api\EventLogger;
use Drupal\shareaholic\Api\ShareaholicApi;
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

  /** @var EventLogger */
  private $eventLogger;

  /** @var ShareaholicApi */
  private $shareaholicApi;

  public function __construct(ConfigEntityStorageInterface $nodeTypeStorage, ShareaholicEntityManager $shareaholicEntityManager, EventLogger $eventLogger, ShareaholicApi $shareaholicApi)
  {
    $this->nodeTypeStorage = $nodeTypeStorage;
    $this->shareaholicEntityManager = $shareaholicEntityManager;
    $this->eventLogger = $eventLogger;
    $this->shareaholicApi = $shareaholicApi;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('entity_type.manager')->getStorage('node_type'),
      $container->get('shareaholic.entity_manager'),
      $container->get('shareaholic.api.event_logger'),
      $container->get('shareaholic.api')
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

    $location = $form_state->getValue('location');

    /** @var NodeTypeInterface $nodeType */
    $nodeType = $this->nodeTypeStorage->load($form_state->getValue('nodeType'));
    $locationType = $form_state->getValue('locationType');

    if (!in_array($locationType, $this->getLocationTypes(), TRUE)) {
      $form_state->setErrorByName('locationType', $this->t("Unknown location type!"));
    }

    if (!empty($form_state->getErrors())) {
      return;
    }

    if (!$this->shareaholicEntityManager->hasLocation($location, $locationType, $nodeType)) {
      $form_state->setErrorByName('location', $this->t('There is no such location!'));
    }

    if ($location === 'default') {
      $form_state->setErrorByName('location', $this->t('Default location is not removable!'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    /** @var NodeTypeInterface $nodeType */
    $nodeType = $this->nodeTypeStorage->load($form_state->getValue('nodeType'));

    $locationType = $form_state->getValue('locationType');

    $location = $form_state->getValue('location');
    $this->shareaholicEntityManager->removeLocation($location, $locationType, $nodeType);

    $this->eventLogger->log($this->eventLogger::EVENT_UPDATED_SETTINGS);

    $this->messenger()->addMessage($this->t("Location '@locationId' type '@locationType' has been added to the node type '@nodeType'!", ['@locationType' => $locationType, '@nodeType' => $nodeType->id(), '@locationId' => $location]));

    $shareButtonsLocations = $this->shareaholicEntityManager->getAllLocations('share_buttons');
    $recommendations = $this->shareaholicEntityManager->getAllLocations('recommendations');
    $sync = $this->shareaholicApi->sync($shareButtonsLocations, $recommendations);
    if (!$sync) {
      $this->messenger()->addMessage($this->t("Synchronization with Shareaholic failed! See logs."), MessengerInterface::TYPE_ERROR);
    } else {
      $this->messenger()->addMessage($this->t("Synchronization with Shareaholic has been successful."));
    }

    $form_state->setRedirect('shareaholic.settings.content');
  }

  private function getLocationTypes(): array {
    return ['share_buttons', 'recommendations'];
  }
}
