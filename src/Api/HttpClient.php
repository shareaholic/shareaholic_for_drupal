<?php

namespace Drupal\shareaholic\Api;

use Drupal;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

/**
 * Class HttpClient
 */
class HttpClient
{
  /** @var Client */
  private $httpClient;

  public function __construct(Client $httpClient) {
    $this->httpClient = $httpClient;
  }

  /**
   * Wrapper around the http client, allowing us to add User-Agent to all outgoing requests.
   *
   * @param $url
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function get($url): ResponseInterface {
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
  public function post($url, $payload): ResponseInterface {
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
   * @return string
   */
  private function getUserAgent(): string {
    return 'Drupal/' . Drupal::VERSION . ' ('. 'PHP/' . PHP_VERSION . '; ' . 'SHR_DRUPAL/' . system_get_info('module', 'shareaholic')['version'] . '; +' . \Drupal::request()->getSchemeAndHttpHost() . ')';
  }
}
