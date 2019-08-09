<?php

namespace Drupal\shareaholic\Api;

use GuzzleHttp\Client;

class ShareaholicApi {
  const API_URL = 'https://www.shareaholic.com';
  const HEALTH_CHECK_URL = self::API_URL . '/haproxy_health_check';
  const KEY_GENERATING_URL = self::API_URL . '/publisher_tools/anonymous';

  /** @var Client */
  private $httpClient;

  public function __construct(Client $httpClient) {
    $this->httpClient = $httpClient;
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
