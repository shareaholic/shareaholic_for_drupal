<?php

namespace Drupal\shareaholic\Api;

use Drupal\Core\Config\ImmutableConfig;
use Psr\Log\LoggerInterface;

/**
 * Class ShareaholicCMApi
 */
class ShareaholicCMApi {
  const CM_API_URL = 'https://cm-web.shareaholic.com';
  const CM_SINGLE_PAGE_REFRESH_URL = self::CM_API_URL . '/jobs/uber_single_page';
  const CM_DOMAIN_REFRESH_URL = self::CM_API_URL . '/jobs/single_domain';

  /** @var HttpClient */
  private $httpClient;

  /** @var LoggerInterface */
  private $logger;

  public function __construct(HttpClient $httpClient, LoggerInterface $logger) {
    $this->httpClient = $httpClient;
    $this->logger = $logger;
  }

  /**
   * Notify Shareaholic Content Manager if content has to be refreshed.
   *
   * @param string $url
   */
  public function singlePageRefresh($url) {
    try {
      $response = $this->httpClient->post(self::CM_SINGLE_PAGE_REFRESH_URL, [
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
      $response = $this->httpClient->post(self::CM_DOMAIN_REFRESH_URL, [
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
}
