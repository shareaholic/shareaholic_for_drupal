<?php

namespace Drupal\shareaholic\Api;

use Drupal\Core\Config\ImmutableConfig;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

class ShareaholicApi {
  const SERVICE_URL = 'https://www.shareaholic.com';
  const API_URL = 'https://web.shareaholic.com';
  const HEALTH_CHECK_URL = self::API_URL . '/haproxy_health_check';
  const KEY_GENERATING_URL = self::API_URL . '/publisher_tools/anonymous';
  const SESSIONS_URL = self::API_URL . '/api/v3/sessions';

  /** @var HttpClient */
  private $httpClient;

  /** @var ImmutableConfig */
  private $config;

  /** @var LoggerInterface */
  private $logger;
  /** @var ShareaholicCMApi */
  private $shareaholicCMApi;

  /** @var EventLogger */
  private $eventLogger;

  public function __construct(HttpClient $httpClient, ImmutableConfig $config, LoggerInterface $logger, ShareaholicCMApi $shareaholicCMApi, EventLogger $eventLogger) {
    $this->httpClient = $httpClient;
    $this->config = $config;
    $this->logger = $logger;
    $this->shareaholicCMApi = $shareaholicCMApi;
    $this->eventLogger = $eventLogger;
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

    $shareButtonsLocationsAttributes = [];
    foreach ($shareButtonsLocations as $shareButtonsLocation) {
      $shareButtonsLocationsAttributes[] = ['name' => $shareButtonsLocation, 'enabled' => TRUE];
    }

    $recommendationsLocationsAttributes = [];
    foreach ($recommendationsLocations as $recommendationsLocation) {
      $recommendationsLocationsAttributes[] = ['name' => $recommendationsLocation, 'enabled' => TRUE];
    }
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
        'site_id' => $this->config->get('api_key'),
        'verification_key' => $this->config->get('verification_key'),
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
      $this->logger->critical("Publisher token couldn't be received. Request status code: $statusCode");
    }

    return NULL;
  }

  /**
   * @return bool
   */
  public function connectivityCheck() {
    $health_check_url = self::HEALTH_CHECK_URL;
    try {
      $response = $this->httpClient->get($health_check_url);
    } catch (\Exception $e) {
      return FALSE;
    }

    return $response->getStatusCode() === 200;
  }

  /**
   * Converts langcode into numeric id
   *
   * @return int|NULL
   */
  private function getLanguageId($langcode) {
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
