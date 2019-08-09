<?php

namespace Drupal\shareaholic\Api;

use Drupal\Core\Config\ImmutableConfig;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;

class ShareaholicApi {
  const SERVICE_URL = 'https://www.shareaholic.com';
  const API_URL = 'https://web.shareaholic.com';
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
   * @return string|null
   */
  public function getPublisherToken() {
    $response = $this->httpClient->post(self::SESSIONS_URL, [
      RequestOptions::JSON => [
        'site_id' => $this->config->get('api_key'),
        'verification_key' => $this->config->get('verification_key'),
      ],
    ]);

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
    $response = $this->httpClient->get($health_check_url);
    return $response->getStatusCode() === 200;
  }

  /**
   * Converts langcode into numeric id
   *
   * @return string
   */
  public function getLanguageId($langcode) {
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
