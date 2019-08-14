<?php

namespace Drupal\shareaholic\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
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

    $path = drupal_get_path('module', 'shareaholic');
    $api_key = $this->shareaholicConfig->get('api_key');

    // add tos window if required
    if (!$api_key) {
      return [
        '#theme' => 'shareaholic_tos',
        '#path' => '/' . $path . '/assets/img',
        '#destination' => Url::fromRoute('shareaholic.settings.content')->toString(),
        '#attached' => [
          'library' => [
            'shareaholic/main',
          ],
          'html_head' => [shareaholic_get_chat_for_head()],
        ],
      ];
    }

    $nodeTypes = $this->nodeTypeStorage->loadMultiple();

    $form['types'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Content types'),
    ];

    /** @var NodeTypeInterface $nodeType */
    foreach ($nodeTypes as $nodeType) {
      $formTypes = &$form['types'];

      $formTypes[$nodeType->id()]['enable_shareaholic'] = [];

      $contentSettingsDescription = [
        '#type' => 'markup',
        '#markup' => Markup::create('<p>' . $this->t("This setting allows you to enable/disable settings per-node. After enabling it, Shareaholic settings will appear in your node's edit/create form. You can use the module without this enabled, if you don't need per-node settings, or you can enable it later." . '</p>')),
      ];

      $contentSettings = [
        '#type' => 'link',
        '#attributes' => [
          'class' => ['button', 'button--secondary'],
        ],
      ];

      if ($this->shareaholicEntityManager->areContentSettingsEnabled($nodeType)) {
        $contentSettings['#title'] = $this->t('Disable Shareaholic Per-Content Settings');
        $contentSettings['#url'] = Url::fromRoute('shareaholic.settings.content.shareaholic_disable', ['nodeType' => $nodeType->id()]);
      } else {
        $contentSettings['#title'] = $this->t('Enable Shareaholic Per-Content Settings');
        $contentSettings['#url'] = Url::fromRoute('shareaholic.settings.content.shareaholic_enable', ['nodeType' => $nodeType->id()]);
      }

      $displayEditLink = Link::createFromRoute($this->t('Handy link'), 'entity.entity_view_display.node.default', ['node_type' => $nodeType->id()])->toString();

      $locationsDescription = [
        '#type' => 'markup',
        '#markup' => Markup::create('<br/><br/><p>' . $this->t("Here you can add more widgets if you need them. Don't forget to make them visible on your content type configuration page! @display_config_link" . '</p>', ['@display_config_link' => $displayEditLink])),
      ];

      $formTypes[$nodeType->id()] = [
        '#type' => 'details',
        '#title' => $nodeType->label(),
        '#open' => TRUE,
        'content_settings_description' => $contentSettingsDescription,
        'enable_content_settings' => $contentSettings,
        //'edit_page' => '',
        'locations_description' => $locationsDescription,
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
    }

    $form['#attached']['html_head'] = [shareaholic_get_chat_for_head()];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
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

    $locationList = $this->shareaholicEntityManager->extractLocations($locationType, $nodeType);
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

    $result[] = [
      '#type' => 'link',
      '#title' => $this->t('Add location'),
      '#attributes' => [
        'class' => ['button', 'button--secondary'],
      ],
      '#url' => Url::fromRoute('shareaholic.settings.content.add_location', ['nodeType' => $nodeType->id(), 'locationType' => $locationType]),
    ];

    return $result;
  }
}
