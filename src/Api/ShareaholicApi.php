<?php

namespace Drupal\shareaholic\Api;

use Drupal;
use Drupal\Core\Config\ImmutableConfig;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class ShareaholicApi {
  const SERVICE_URL = 'https://www.shareaholic.com';
  const API_URL = 'https://web.shareaholic.com';
  const CM_API_URL = 'https://cm-web.shareaholic.com';
  const CM_SINGLE_PAGE_REFRESH_URL = self::CM_API_URL . '/jobs/uber_single_page';
  const CM_DOMAIN_REFRESH_URL = self::CM_API_URL . '/jobs/single_domain';
  const HEALTH_CHECK_URL = self::API_URL . '/haproxy_health_check';
  const KEY_GENERATING_URL = self::API_URL . '/publisher_tools/anonymous';
  const EVENTS_URL = self::API_URL . '/api/events';
  const SESSIONS_URL = self::API_URL . '/api/v3/sessions';

  /** @var Client */
  private $httpClient;

  /** @var ImmutableConfig */
  private $config;

  /** @var LoggerInterface */
  private $logger;

  public function __construct(Client $httpClient, ImmutableConfig $config, LoggerInterface $logger) {
    $this->httpClient = $httpClient;
    $this->config = $config;
    $this->logger = $logger;
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

    try {
      $response = $this->post(static::KEY_GENERATING_URL, [
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
        $this->logger->critical("Couldn't generate an API key. Response had no body.");
        return NULL;
      }

    } catch (RequestException $e) {
      $errorCode = $e->getCode();
      $errorMessage = $e->getMessage();

      $this->logger->critical("Error code: $errorCode, message: $errorMessage");
      return NULL;
    }

    $json_response = json_decode($data, TRUE);
    $this->domainRefresh();
    return $json_response['api_key'];
  }

  /**
   * @return string|null
   */
  public function getJwtToken() {

    try {
      $response = $this->post(self::SESSIONS_URL, [
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
      $response = $this->get($health_check_url);
    } catch (\Exception $e) {
      return FALSE;
    }

    return $response->getStatusCode() === 200;
  }

  /**
   * Notify Shareaholic Content Manager if content has to be refreshed.
   *
   * @param string $url
   */
  public function singlePageRefresh($url) {
    try {
      $response = $this->post(self::CM_SINGLE_PAGE_REFRESH_URL, [
        'args' => [$url, ['force' => TRUE]],
      ]);
    } catch (\Exception $exception) {
      $this->logger->critical("Clearing Shareaholic cache of a '$url' page failed. Connection failed.");
      return;
    }

    $statusCode = $response->getStatusCode();
    if ($statusCode !== 200) {
      $this->logger->critical("Clearing Shareaholic cache of a '$url' page failed. Status code returned: $statusCode.");
    }
  }

  /**
   * Notify Shareaholic Content Manager if domain has to be refreshed.
   */
  public function domainRefresh() {
    try {
      $response = $this->post(self::CM_DOMAIN_REFRESH_URL, [
        'args' => [\Drupal::request()->getSchemeAndHttpHost(), ['force' => TRUE]],
      ]);
    } catch (\Exception $exception) {
      $this->logger->critical("Clearing Shareaholic domain cache in Content Manager failed. Connection failed.");
      return;
    }

    $statusCode = $response->getStatusCode();
    if ($statusCode !== 200) {
      $this->logger->critical("Clearing Shareaholic domain cache in Content Manager failed. Status code returned: $statusCode.");
    }
  }

  /**
   * @return string
   */
  private function getUserAgent(): string {
    return 'Drupal/' . Drupal::VERSION . ' ('. 'PHP/' . PHP_VERSION . '; ' . 'SHR_DRUPAL/' . system_get_info('module', 'shareaholic')['version'] . '; +' . \Drupal::request()->getSchemeAndHttpHost() . ')';
  }

  /**
   * Wrapper around the http client, allowing us to add User-Agent to all outgoing requests.
   *
   * @param $url
   * @return \Psr\Http\Message\ResponseInterface
   */
  private function get($url): ResponseInterface {
    return $this->httpClient->get($url, [
      'headers' => $this->getDefaultHeaders(),
    ]);
  }

  /**
   * Wrapper around the http client, allowing us to add User-Agent to all outgoing requests.
   *
   * @param $url
   * @param $payload
   * @return \Psr\Http\Message\ResponseInterface
   */
  private function post($url, $payload): ResponseInterface {
    return $this->httpClient->post($url, [
      RequestOptions::JSON => $payload,
      'headers' => $this->getDefaultHeaders(),
    ]);
  }

  /**
   * @return array
   */
  private function getDefaultHeaders(): array {
    return [
      'User-Agent' => $this->getUserAgent(),
    ];
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
