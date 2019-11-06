<?php

namespace Drupal\shareaholic\Api;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\shareaholic\Helper\DiagnosticsProvider;
use Drupal\shareaholic\Helper\ShareaholicEntityManager;
use Psr\Log\LoggerInterface;

class EventLogger {

  const EVENTS_URL = ShareaholicApi::API_URL . '/api/events';

  const EVENT_UPGRADE = 'Upgrade';
  const EVENT_INSTALL_FRESH = 'Install_Fresh';
  const EVENT_UNINSTALL = 'Uninstall';
  const EVENT_ACCEPTED_TOS = 'AcceptedToS';
  const EVENT_FAILED_TO_CREATE_API_KEY = 'FailedToCreateApiKey';
  const EVENT_UPDATED_SETTINGS = 'UpdatedSettings';

  /** @var ThemeManagerInterface */
  private $themeManager;

  /** @var ImmutableConfig */
  private $config;

  /** @var HttpClient */
  private $httpClient;

  /** @var LoggerInterface */
  private $logger;

  /** @var ShareaholicEntityManager */
  private $shareaholicEntityManager;

  /** @var DiagnosticsProvider */
  private $diagnosticsProvider;

  public function __construct(ThemeManagerInterface $themeManager, ImmutableConfig $config, HttpClient $httpClient, LoggerInterface $logger, ShareaholicEntityManager $shareaholicEntityManager, DiagnosticsProvider $diagnosticsProvider) {
    $this->themeManager = $themeManager;
    $this->config = $config;
    $this->httpClient = $httpClient;
    $this->logger = $logger;
    $this->shareaholicEntityManager = $shareaholicEntityManager;
    $this->diagnosticsProvider = $diagnosticsProvider;
  }

  /**
   * @param string $event_name
   * @param string $previousVersion
   */
  public function logUpgrade($event_name, $previousVersion) {
    $this->log($event_name, ['previous_plugin_version' => $previousVersion]);
  }

  /**
   * @param $event_name
   * @param $reason
   */
  public function logWithReason($event_name, $reason) {
    $this->log($event_name, ['reason' => $reason]);
  }

  /**
   * This is a wrapper for the Event API
   *
   * @param string $event_name the name of the event
   * @param array $extra_params any extra data points to be included
   */
  public function log($event_name, array $extra_params = []) {

    /*
     * Put locations from all node types into locationType specific arrays.
     */

    $shareButtonsLocations = [];
    $locationsArray = $this->shareaholicEntityManager->getAllLocations('share_buttons');
    foreach ($locationsArray as $location) {
      $shareButtonsLocations[$location] = 'on';
    }

    $recomendationsLocations = [];
    $locationsArray = $this->shareaholicEntityManager->getAllLocations('recommendations');
    foreach ($locationsArray as $location) {
      $recomendationsLocations[$location] = 'on';
    }

    $event_metadata = [
      'plugin_version' => system_get_info('module', 'shareaholic')['version'],
      'api_key' => $this->config->get('api_key'),
      'domain' => \Drupal::request()->getHost(),
      'diagnostics' => [
        'php_version' => PHP_VERSION,
        'drupal_version' => \Drupal::VERSION,
        'theme' => $this->themeManager->getActiveTheme()->getName(),
        'multisite' => $this->diagnosticsProvider->isMultisite(),
      ],
      'features' => [
        'share_buttons' => $shareButtonsLocations,
        'recommendations' => $recomendationsLocations,
      ],
    ];

    $event_metadata = array_merge($event_metadata, $extra_params);

    $event_params = [
      'name' => "Drupal:" . $event_name,
      'data' => json_encode($event_metadata),
    ];

    try {
      $response = $this->httpClient->post(self::EVENTS_URL, $event_params);
    } catch (\Exception $exception) {
      $code = $exception->getCode();
      $message = $exception->getMessage();
      $this->logger->critical("Couldn't send event to the Shareaholic. Exception code: $code. Message: $message");
      return;
    }

    $responseStatus = $response->getStatusCode();
    if ($responseStatus !== 200) {
      $this->logger->critical("Couldn't send event to the Shareaholic. Server not available. Response's status code: $responseStatus");
    }
  }
}
