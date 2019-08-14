<?php

namespace Drupal\shareaholic\Api;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
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

  /** @var ModuleHandlerInterface */
  private $moduleHandler;

  /** @var ThemeManagerInterface */
  private $themeManager;

  /** @var Connection */
  private $connection;

  /** @var ImmutableConfig */
  private $config;

  /** @var HttpClient */
  private $httpClient;

  /** @var LoggerInterface */
  private $logger;

  /** @var ShareaholicEntityManager */
  private $shareaholicEntityManager;

  public function __construct(ModuleHandlerInterface $moduleHandler, ThemeManagerInterface $themeManager, Connection $connection, ImmutableConfig $config, HttpClient $httpClient, LoggerInterface $logger, ShareaholicEntityManager $shareaholicEntityManager) {
    $this->moduleHandler = $moduleHandler;
    $this->themeManager = $themeManager;
    $this->connection = $connection;
    $this->config = $config;
    $this->httpClient = $httpClient;
    $this->logger = $logger;
    $this->shareaholicEntityManager = $shareaholicEntityManager;
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
    $nodeTypes = $this->shareaholicEntityManager->getShareaholicEnabledNodeTypes();
    $recomendationsLocations = [];
    $shareButtonsLocations = [];
    foreach ($nodeTypes as $nodeType) {
      $locationsArray = $this->shareaholicEntityManager->extractLocations('share_buttons', $nodeType);
      foreach ($locationsArray as $location) {
        $shareButtonsLocations[$location] = 'on';
      }

      $locationsArray = $this->shareaholicEntityManager->extractLocations('recommendations', $nodeType);
      foreach ($locationsArray as $location) {
        $recomendationsLocations[$location] = 'on';
      }
    }

    $event_metadata = [
      'plugin_version' => system_get_info('module', 'shareaholic')['version'],
      'api_key' => $this->config->get('api_key'),
      'domain' => \Drupal::request()->getHost(),
      'language' => \Drupal::languageManager()->getCurrentLanguage()->getId(),
      'stats' => $this->getStats(),
      'diagnostics' => [
        'php_version' => PHP_VERSION,
        'drupal_version' => \Drupal::VERSION,
        'theme' => $this->themeManager->getActiveTheme()->getName(),
        'active_plugins' => array_keys($this->moduleHandler->getModuleList()),
      ],
      'features' => [
        'share_buttons' => $shareButtonsLocations,
        'recommendations' => $recomendationsLocations,
      ],
    ];

    if ($extra_params) {
      $event_metadata = array_merge($event_metadata, $extra_params);
    }

    $event_params = [
      'name' => "Drupal:" . $event_name,
      'data' => $event_metadata,
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

  /**
   * Get the total number of comments for this site
   *
   * @return integer
   *   The total number of comments
   */
  public function totalComments() {
    if (!$this->connection->schema()->tableExists('comment')) {
      return 0;
    }

    return $this->connection->query("SELECT count(cid) FROM {comment}")->fetchField();
  }

  /**
   * Get the stats for this website
   * Stats include: total number of pages by type, total comments, total users
   *
   * @return array an associative array of stats => counts
   */
  public function getStats() {
    $stats = [];
    // Query the database for content types and add to stats
    $result = $this->connection->query("SELECT type, count(*) as count FROM {node} GROUP BY type");
    foreach ($result as $record) {
      $stats[$record->type . '_total'] = $record->count;
    }

    // Get the total comments
    $stats['comments_total'] = $this->totalComments();
    return $stats;
  }
}
