<?php

namespace Drupal\shareaholic\Api;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\shareaholic\Helper\DiagnosticsProvider;
use Drupal\shareaholic\Helper\StatisticsProvider;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

class ShareaholicApi {
  const SERVICE_URL = 'https://www.shareaholic.com';
  const API_URL = 'https://web.shareaholic.com';
  const HEALTH_CHECK_URL = self::API_URL . '/haproxy_health_check';
  const KEY_GENERATING_URL = self::API_URL . '/publisher_tools/anonymous';
  const SESSIONS_URL = self::API_URL . '/api/v3/sessions';
  const HEARTBEAT_URL = self::API_URL . '/api/plugin_heartbeats';

  /** @var HttpClient */
  private $httpClient;

  /** @var ImmutableConfig */
  private $shareaholicConfig;

  /** @var LoggerInterface */
  private $logger;

  /** @var ShareaholicCMApi */
  private $shareaholicCMApi;

  /** @var EventLogger */
  private $eventLogger;

  /** @var StatisticsProvider */
  private $statsProvider;

  /** @var DiagnosticsProvider */
  private $diagnosticsProvider;

  public function __construct(HttpClient $httpClient, ImmutableConfig $shareaholicConfig, LoggerInterface $logger, ShareaholicCMApi $shareaholicCMApi, EventLogger $eventLogger, StatisticsProvider $statsProvider, DiagnosticsProvider $diagnosticsProvider) {
    $this->httpClient = $httpClient;
    $this->shareaholicConfig = $shareaholicConfig;
    $this->logger = $logger;
    $this->shareaholicCMApi = $shareaholicCMApi;
    $this->eventLogger = $eventLogger;
    $this->statsProvider = $statsProvider;
    $this->diagnosticsProvider = $diagnosticsProvider;
  }

  /**
   * @return string
   */
  public function getSyncURL(): string {
    $apiKey = $this->shareaholicConfig->get('api_key');
    return self::API_URL . "/publisher_tools/$apiKey/sync";
  }

  /**
   * @return string
   */
  public function getSyncStatusURL(): string {
    $apiKey = $this->shareaholicConfig->get('api_key');
    return self::API_URL . "/publisher_tools/$apiKey/sync/status";
  }

  /**
   * @param $verificationKey
   * @param $siteName
   * @param $langcode
   *
   * @param string[] $shareButtonsLocations
   * @param string[] $recommendationsLocations
   *
   * @return string|null
   */
  public function generateApiKey($verificationKey, $siteName, $langcode, $shareButtonsLocations = [], $recommendationsLocations = []) {

    /*
     * Transform arrays into ones expected by the API.
     */

    $shareButtonsLocationsAttributes = $this->prepareLocationsArray($shareButtonsLocations);
    $recommendationsLocationsAttributes = $this->prepareLocationsArray($recommendationsLocations);

    $error = NULL;

    try {
      $response = $this->httpClient->post(static::KEY_GENERATING_URL, [
        'configuration_publisher' => [
          'verification_key' => $verificationKey,
          'site_name' => $siteName,
          'domain' => \Drupal::request()->getHost(),
          'platform_id' => '2',
          'language_id' => $this->getLanguageId($langcode),
          'shortener' => 'shrlc',
          'share_buttons_attributes' => [
            'locations_attributes' => $shareButtonsLocationsAttributes,
          ],
          'recommendations_attributes' => [
            'locations_attributes' => $recommendationsLocationsAttributes,
          ],
        ],
      ]);
      $data = (string) $response->getBody();

      if (empty($data)) {
        $error = "Couldn't generate an API key. Response had no body.";
      }

    } catch (RequestException $e) {
      $errorCode = $e->getCode();
      $errorMessage = $e->getMessage();
      $error = "Error code: $errorCode, message: $errorMessage";
    }


    if ($error) {
      $this->logger->critical($error);
      $this->eventLogger->logWithReason($this->eventLogger::EVENT_FAILED_TO_CREATE_API_KEY, $error);
      return NULL;
    }

    $json_response = json_decode($data, TRUE);
    $this->shareaholicCMApi->domainRefresh();
    return $json_response['api_key'];
  }

  /**
   * @return string|null
   */
  public function getJwtToken() {

    try {
      $response = $this->httpClient->post(self::SESSIONS_URL, [
        'site_id' => $this->shareaholicConfig->get('api_key'),
        'verification_key' => $this->shareaholicConfig->get('verification_key'),
      ]);
    } catch (\Exception $exception) {
      $this->logger->critical("Publisher token couldn't be received. Couldn't connect to the server.");
      return NULL;
    }

    $statusCode = $response->getStatusCode();

    if (preg_match('/20*/', $statusCode)) {
      $body = json_decode($response->getBody()->getContents(), TRUE);
      if (!empty($body['publisher_token'])) {
        return $body['publisher_token'];
      }
      $this->logger->critical("Publisher token couldn't be received. Wrong content of server's response.");
    } else {
      $this->logger->critical("Publisher token couldn't be received. HTTP status code: $statusCode");
    }

    return NULL;
  }

  /**
   * @param string[] $shareButtonsLocations
   * @param string[] $recommendationsLocations
   *
   * @return bool
   */
  public function sync(array $shareButtonsLocations, array $recommendationsLocations): bool {

    try {
      $response = $this->httpClient->post($this->getSyncURL(), [
        'verification_key' => $this->shareaholicConfig->get('verification_key'),
        'app_locations' => [
          'share_buttons' => $this->prepareLocationsHash($shareButtonsLocations),
          'recommendations' => $this->prepareLocationsHash($recommendationsLocations),
        ],
      ]);

      $body = json_decode($response->getBody()->getContents(), TRUE);
      $statusCode = $response->getStatusCode();

      if ($statusCode === 200 && $body['status'] === 'success') {
        return TRUE;
      }

      $this->logger->critical("Sync status check failed. HTTP status code: $statusCode.");
    } catch(\Exception $exception) {
      $code = $exception->getCode();
      $message = $exception->getMessage();
      $this->logger->critical("Sync status check failed. Couldn't connect to the server. Code: $code. Message: $message");
    }

    return FALSE;
  }

  /**
   * @param array $shareButtonsLocations
   * @param array $recommendationsLocations
   *
   * @return bool|null
   */
  public function syncStatus(array $shareButtonsLocations, array $recommendationsLocations) {
    try {
      $response = $this->httpClient->post($this->getSyncStatusURL(), [
        'verification_key' => $this->shareaholicConfig->get('verification_key'),
        'app_locations' => [
          'share_buttons' => $this->prepareLocationsHash($shareButtonsLocations),
          'recommendations' => $this->prepareLocationsHash($recommendationsLocations),
        ],
      ]);


      $body = json_decode($response->getBody()->getContents(), TRUE);
      $statusCode = $response->getStatusCode();

      if ($statusCode === 200 && $body['status'] === 'success') {
        return TRUE;
      }

      $this->logger->critical("Sync status check failed. HTTP status code: $statusCode.");
    } catch(\Exception $exception) {
      $code = $exception->getCode();
      if ($code === 409) {
        return FALSE;
      }
      $message = $exception->getMessage();
      $this->logger->critical("Sync status check failed. Couldn't connect to the server. Code: $code. Message: $message");
    }

    return NULL;
  }

  /**
   * This method is sending more hefty diagnostics data to Shareaholic.
   */
  public function heartbeat() {
    $data = [
      'platform' => 'drupal',
      'plugin_name' => 'shareaholic',
      'plugin_version' => system_get_info('module', 'shareaholic')['version'],
      'api_key' => $this->shareaholicConfig->get('api_key'),
      'verification_key' => $this->shareaholicConfig->get('verification_key'),
      'domain' => \Drupal::request()->getHost(),
      'language' => \Drupal::languageManager()->getCurrentLanguage()->getId(),
      'stats' => $this->statsProvider->getStats(),
      'diagnostics' => [
        'tos_status' => $this->shareaholicConfig->get('has_accepted_tos'),
        'shareaholic_server_reachable' => $this->connectivityCheck(),
        'php_version' => PHP_VERSION,
        'drupal_version' => \Drupal::VERSION,
        'theme' => $this->diagnosticsProvider->getActiveTheme(),
        'multisite' => $this->diagnosticsProvider->isMultisite(),
        'plugins' => [
          'active' => $this->diagnosticsProvider->getActivePlugins(),
        ],
      ],
      'advanced_settings' => [
        'disable_og_tags' => !$this->shareaholicConfig->get('enable_og_tags'),
      ],
    ];

    try {
      $this->httpClient->post($this::HEARTBEAT_URL, $data);
    } catch (\Exception $exception) {
      $code = $exception->getCode();
      $message = $exception->getMessage();
      $this->logger->critical("Couldn't send heartbeat to Shareaholic. Exception code: $code. Message: $message");
      return;
    }
  }

  /**
   * @return bool
   */
  public function connectivityCheck(): bool {
    $health_check_url = self::HEALTH_CHECK_URL;
    try {
      $response = $this->httpClient->get($health_check_url);
    } catch (\Exception $e) {
      return FALSE;
    }

    return $response->getStatusCode() === 200;
  }

  /**
   * @param $locations
   *
   * @return array
   */
  private function prepareLocationsArray($locations): array {
    $result = [];
    foreach ($locations as $location) {
      $result[] = ['name' => $location, 'enabled' => TRUE];
    }

    return $result;
  }

  /**
   * Prepare associative array of locations, and their parameters.
   *
   * @param $locations
   * @return array
   */
  private function prepareLocationsHash(array $locations): array {
    $result = [];
    foreach ($locations as $location) {
      $result[$location] = ['enabled' => TRUE];
    }

    return $result;
  }

  /**
   * Converts langcode into numeric id
   *
   * @param string $langcode
   *
   * @return int|NULL
   */
  private function getLanguageId(string $langcode) {
    $language_id_map = [
      "ar" => 1, // Arabic
      "bg" => 2, // Bulgarian
      "zh-hans" => 3, // Chinese (Simplified)
      "zh-hant" => 4, // Chinese (Traditional)
      "hr" => 5, // Croatian
      "cs" => 6, // Czech
      "da" => 7, // Danish
      "nl" => 8, // Netherlands
      "en" => 9, // English
      "et" => 10, // Estonian
      "fi" => 11, // Finnish
      "fr" => 12,  // French
      "de" => 13,  // German
      "el" => 14,  // Greek
      "he" => 15,  // Hebrew
      "hu" => 16,  // Hungarian
      "id" => 17,  // Indonesian
      "it" => 18,  // Italian
      "ja" => 19,  // Japanese
      "ko" => 20,  // Korean
      "lv" => 21,  // Lativan
      "lt" => 22,  // Lithuanian
      "nn" => 23,  // Norwegian
      "pl" => 24,  // Poland
      "pt-pt" => 25, // Portuguese
      "ro" => 26,    // Romanian
      "ru" => 27,    // Russian
      "sr" => 28,    // Serbian
      "sk" => 29,    // Slovak
      "sl" => 30,    // Slovenian
      "es" => 31,    // Spanish
      "sv" => 32,    // Swedish
      "th" => 33,    // Thai
      "tr" => 34,    // Turkish
      "uk" => 35,    // Ukrainian
      "vi" => 36,    // Vietnamese
    ];
    return $language_id_map[$langcode] ?? NULL;
  }
}
