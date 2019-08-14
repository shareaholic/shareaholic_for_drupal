<?php

namespace Drupal\shareaholic\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\node\NodeTypeInterface;
use Drupal\shareaholic\Api\EventLogger;
use Drupal\shareaholic\Helper\ShareaholicEntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContentSettingsForm.
 */
class ContentSettingsForm extends FormBase {


  /** @var CacheBackendInterface */
  private $renderCache;

  /** @var Config */
  private $shareaholicConfig;

  /** @var ConfigEntityStorageInterface */
  private $nodeTypeStorage;

  /** @var ShareaholicEntityManager */
  private $shareaholicEntityManager;

  /** @var EventLogger */
  private $eventLogger;

  public function __construct(CacheBackendInterface $renderCache, Config $config, ConfigEntityStorageInterface $nodeTypeStorage, ShareaholicEntityManager $shareaholicEntityManager, EventLogger $eventLogger)
  {
    $this->renderCache = $renderCache;
    $this->shareaholicConfig = $config;
    $this->nodeTypeStorage = $nodeTypeStorage;
    $this->shareaholicEntityManager = $shareaholicEntityManager;
    $this->eventLogger = $eventLogger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cache.render'),
      $container->get('shareaholic.editable_config'),
      $container->get('entity_type.manager')->getStorage('node_type'),
      $container->get('shareaholic.entity_manager'),
      $container->get('shareaholic.api.event_logger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'shareaholic_content_settings_form';
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

    $nodeTypes = $this->nodeTypeStorage->loadMultiple();

    $this->messenger()->addMessage($this->t('After setting up your locations, clear cache for changes to be applied to the node types.'));

    $form['types'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Content types'),
    ];

    /** @var NodeTypeInterface $nodeType */
    foreach ($nodeTypes as $nodeType) {
      $formTypes = &$form['types'];

      $formTypes[$nodeType->id()] = [
        '#type' => 'details',
        '#title' => $nodeType->label(),
        '#open' => TRUE,
        'share_buttons' => [
          '#type' => 'details',
          '#title' => $this->t('Share buttons'),
          '#open' => TRUE,
          'items' => $this->renderLocationsSettings($nodeType, 'share_buttons'),
        ],
        'recommendations' => [
          '#type' => 'details',
          '#title' => $this->t('Recommendations.'),
          '#open' => TRUE,
          'items' => $this->renderLocationsSettings($nodeType, 'recommendations'),
        ],
      ];

      try {
        $isShareaholicEnabled = $this->shareaholicEntityManager->isShareaholicEnabled($nodeType);
      } catch (\LogicException $exception) {
        $enableShareaholic = [
          '#type' => 'markup',
          '#markup' => Markup::create($this->t('Error. Too many shareaholic fields attached to the content type.')),
        ];
        continue;
      }

      $formTypes[$nodeType->id()]['enable_shareaholic'] = [];
      $enableShareaholic = &$formTypes[$nodeType->id()]['enable_shareaholic'];

      if ($isShareaholicEnabled) {
        $enableShareaholic = [
          '#type' => 'link',
          '#title' => $this->t('Disable Shareaholic'),
          '#attributes' => [
            'class' => ['button', 'button--secondary'],
          ],
          '#url' => Url::fromRoute('shareaholic.settings.content.shareaholic_disable', ['nodeType' => $nodeType->id()]),
        ];
      } else {
        $enableShareaholic = [
          '#type' => 'link',
          '#title' => $this->t('Enable Shareaholic'),
          '#attributes' => [
            'class' => ['button', 'button--secondary'],
          ],
          '#url' => Url::fromRoute('shareaholic.settings.content.shareaholic_enable', ['nodeType' => $nodeType->id()]),
        ];
      }
    }

    $form['#attached']['html_head'] = [shareaholic_get_chat_for_head()];

    return $form;
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

    $this->eventLogger->log($this->eventLogger::EVENT_UPDATED_SETTINGS);
    parent::submitForm($form, $form_state);
  }

  /**
   * @return string[]
   */
  public static function getLocationTypes(): array {
    return ['share_buttons', 'recommendations'];
  }

  /**
   * @param \Drupal\node\NodeTypeInterface $nodeType
   * @param string $locationType
   * @return array
   */
  private function renderLocationsSettings(NodeTypeInterface $nodeType, $locationType): array {
    $locationList = $nodeType->getThirdPartySetting('shareaholic', "locations_$locationType", []);
    $result = [];
    foreach ($locationList as $key => $locationId) {

      $location = [
        '#type' => 'details',
        '#title' => $locationId,
        '#open' => TRUE,
      ];


      if ($key === 0) {
        $location['remove'] = [
          '#type' => 'markup',
          '#markup' => Markup::create($this->t("This is a default location that is not removable.")),
        ];
      } else {
        $location['remove'] = [
          '#type' => 'link',
          '#title' => $this->t('Remove'),
          '#attributes' => [
            'class' => ['button', 'button--secondary'],
          ],
          '#url' => Url::fromRoute('shareaholic.settings.content.remove_location', ['nodeType' => $nodeType->id(), 'locationType' => $locationType, 'location' => $locationId]),
        ];
      }

      $result[] =  $location;
    }

    if (empty($result)) {
      $result[] = [
        '#type' => 'markup',
        '#markup' => Markup::create($this->t('No locations')),
      ];
    }

    if ($this->shareaholicEntityManager->isShareaholicEnabled($nodeType)) {
      $result[] = [
        '#type' => 'link',
        '#title' => $this->t('Add location'),
        '#attributes' => [
          'class' => ['button', 'button--secondary'],
        ],
        '#url' => Url::fromRoute('shareaholic.settings.content.add_location', ['nodeType' => $nodeType->id(), 'locationType' => $locationType]),
      ];
    }

    return $result;
  }
}
