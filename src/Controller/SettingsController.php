<?php

namespace Drupal\shareaholic\Controller;

use Drupal\Core\Config\Config;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\shareaholic\Api\ShareaholicApi;
use Drupal\shareaholic\Form\ContentSettingsForm;
use Drupal\shareaholic\Helper\ShareaholicEntityManager;
use Drupal\shareaholic\Helper\TOSManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class SettingsController.
 */
class SettingsController extends ControllerBase {

  /** @var ShareaholicApi */
  private $shareaholicApi;

  /** @var TOSManager */
  private $TOSManager;

  /** @var Config */
  private $shareaholicConfig;

  /** @var ShareaholicEntityManager */
  private $shareaholicEntityManager;

  public function __construct(ShareaholicApi $shareaholicApi, TOSManager $TOSManager, Config $config, ShareaholicEntityManager $shareaholicEntityManager)
  {
    $this->shareaholicApi = $shareaholicApi;
    $this->TOSManager = $TOSManager;
    $this->shareaholicConfig = $config;
    $this->shareaholicEntityManager = $shareaholicEntityManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('shareaholic.api'),
      $container->get('shareaholic.tos_manager'),
      $container->get('shareaholic.editable_config'),
      $container->get('shareaholic.entity_manager')
    );
  }

  /**
   * Config Page.
   */
  public function configPage() {

    $path = drupal_get_path('module', 'shareaholic');
    $api_key = $this->shareaholicConfig->get('api_key');

    // add tos window if required
    if (!$api_key) {
      return [
        '#theme' => 'shareaholic_tos',
        '#path' => '/' . $path . '/assets/img',
        '#destination' => Url::fromRoute('shareaholic.settings')->toString(),
        '#attached' => [
          'library' => [
            'shareaholic/main',
          ],
          'html_head' => [shareaholic_get_chat_for_head()],
        ],
      ];
    }

    if (empty($this->shareaholicEntityManager->getShareaholicEnabledNodeTypes())) {
      $this->messenger()->addMessage($this->t("Remember to enable Shareaholic for your nodes on the Content Settings page!"));
    }

    $jwtToken =  $this->shareaholicApi->getJwtToken();
    if (!$jwtToken) {
      $this->messenger()->addMessage("Publisher token couldn't be received. See log.", MessengerInterface::TYPE_ERROR);
      return [
        '#attached' => [
          'html_head' => [shareaholic_get_chat_for_head()],
        ],
      ];
    }

    return [
      '#theme' => 'shareaholic_settings',
      '#apiKey' => $this->shareaholicConfig->get('api_key'),
      '#jwtToken' => $jwtToken,
      '#apiHost' => $this->shareaholicApi::API_URL,
      '#serviceHost' => $this->shareaholicApi::SERVICE_URL,
      '#assetHost' => Settings::get('shareaholic_assets_host', 'https://cdn.shareaholic.net/'),
      '#language' => $this->languageManager()->getCurrentLanguage()->getId(),
      '#attached' => [
        'html_head' => [shareaholic_get_chat_for_head()],
        'library' => [
          'shareaholic/main',
        ],
      ],
    ];
  }

  /**
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function generateKey() {
    $verification_key = md5(mt_rand());

    $siteName = $this->config('system.site')->get('name');
    $langcode = $this->languageManager()->getCurrentLanguage()->getId();

    $shareButtonsLocations = [];
    $recommendationsLocations = [];

    $nodeTypes = $this->shareaholicEntityManager->getShareaholicEnabledNodeTypes();
    foreach ($nodeTypes as $nodeType) {
      $shareButtonsLocations = array_merge($shareButtonsLocations, $this->shareaholicEntityManager->extractLocations('share_buttons', $nodeType));
      $recommendationsLocations = array_merge($recommendationsLocations, $this->shareaholicEntityManager->extractLocations('recommendations', $nodeType));
    }

    $apiKey = $this->shareaholicApi->generateApiKey($verification_key, $siteName, $langcode, $shareButtonsLocations, $recommendationsLocations);

    if ($apiKey) {
      $this->updateOptions([
        'api_key' => $apiKey,
        'verification_key' => $verification_key,
      ]);

      $this->TOSManager->acceptTermsOfService();
    } else {
      $this->messenger()->addMessage("Couldn't generate API key. See log.", MessengerInterface::TYPE_ERROR);
    }

    $destination = \Drupal::request()->get('destination');

    if (!$destination) {
      $destination = Url::fromRoute('shareaholic.settings')
        ->setAbsolute()
        ->toString();
    }

    return new RedirectResponse($destination);
  }

  /**
   * Update multiple keys of the settings object
   * Works like the Wordpress function for Shareaholic
   *
   * @param array $array an array of options to update
   *
   * @return bool
   */
  private function updateOptions($array) {
    foreach ($array as $key => $setting) {
      $this->shareaholicConfig->set($key, $setting)->save();
    }
  }
}
