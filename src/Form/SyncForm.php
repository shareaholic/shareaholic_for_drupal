<?php

namespace Drupal\shareaholic\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Markup;
use Drupal\shareaholic\Api\EventLogger;
use Drupal\shareaholic\Api\ShareaholicApi;
use Drupal\shareaholic\Helper\ShareaholicEntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SyncForm.
 */
class SyncForm extends FormBase {

  /** @var ShareaholicEntityManager */
  private $shareaholicEntityManager;

  /** @var EventLogger */
  private $eventLogger;

  /** @var ShareaholicApi */
  private $shareaholicApi;

  public function __construct(ShareaholicEntityManager $shareaholicEntityManager, EventLogger $eventLogger, ShareaholicApi $shareaholicApi)
  {
    $this->shareaholicEntityManager = $shareaholicEntityManager;
    $this->eventLogger = $eventLogger;
    $this->shareaholicApi = $shareaholicApi;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
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

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => Markup::create($this->t("Are you sure you want to send locations existing on your website to Shareaholic for synchronization? It will allow you to configure them.")),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Synchronize'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $shareButtonsLocations = $this->shareaholicEntityManager->getAllLocations('share_buttons');
    $recommendations = $this->shareaholicEntityManager->getAllLocations('recommendations');

    $result = $this->shareaholicApi->sync($shareButtonsLocations, $recommendations);

    if (!$result) {
      $this->messenger()->addMessage($this->t('Synchronization failed! See log.'), MessengerInterface::TYPE_ERROR);
    } else {
      $this->messenger()->addMessage($this->t("Synchronization with Shareaholic has been successful."));
    }

    $form_state->setRedirect('shareaholic.settings.content');
  }
}
