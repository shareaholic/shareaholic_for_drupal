<?php

namespace Drupal\shareaholic\Form;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Markup;
use Drupal\node\NodeTypeInterface;
use Drupal\shareaholic\Api\EventLogger;
use Drupal\shareaholic\Api\ShareaholicApi;
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

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => Markup::create($this->t("Are you sure you want to add a new location of the type '@locationType' to the content type '@nodeType'?", ['@locationType' => $locationType, '@nodeType' => $nodeType->id()])),
    ];

    $form['location'] = [
      '#type' => 'machine_name',
      '#title' => $this->t("Location's name. It will be prefixed with: '@nodeType_'", ['@nodeType' => $nodeType->id()]),
      '#required' => TRUE,
      '#machine_name' => [
        'exists' => [$this, 'locationExists'],
      ],
      '#label' => 'fgfg',
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

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $locationType = $form_state->getValue('locationType');

    if (!in_array($locationType, ContentSettingsForm::getLocationTypes(), TRUE)) {
      $form_state->setErrorByName('locationType', $this->t("Unknown location type!"));
    }

    if (!empty($form_state->getErrors())) {
      return;
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
       $this->eventLogger->log($this->eventLogger::EVENT_UPDATED_SETTINGS);

    $this->messenger()->addMessage($this->t("Location type '@locationType' of id '@locationId' has been added to the node type '@nodeType'!", ['@locationType' => $locationType, '@nodeType' => $nodeType->id(), '@locationId' => $locationId]));

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

  /**
   * Callback ensuring that location will be unique.
   *
   * @param $value
   * @param $element
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return bool
   */
  public function locationExists($value, $element, FormStateInterface $form_state): bool {
    /** @var NodeTypeInterface $nodeType */
    $nodeType = $this->nodeTypeStorage->load($form_state->getValue('nodeType'));
    $locationType = $form_state->getValue('locationType');

    return $this->shareaholicEntityManager->hasLocation(ShareaholicEntityManager::createLocationName($nodeType->id(), $value), $locationType, $nodeType);
  }
}
