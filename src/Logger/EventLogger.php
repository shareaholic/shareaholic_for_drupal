<?php

namespace Drupal\shareaholic\Logger;

use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\shareaholic\Api\ShareaholicApi;

class EventLogger {

  /** @var ModuleHandlerInterface */
  private $moduleHandler;

  /** @var ThemeManagerInterface */
  private $themeManager;

  /** @var Connection */
  private $connection;

  /** @var ShareaholicApi */
  private $shareaholicApi;

  public function __construct(ModuleHandlerInterface $moduleHandler, ThemeManagerInterface $themeManager, Connection $connection, ShareaholicApi $shareaholicApi)
  {
    $this->moduleHandler = $moduleHandler;
    $this->themeManager = $themeManager;
    $this->connection = $connection;
    $this->shareaholicApi = $shareaholicApi;
  }

  /**
   * This is a wrapper for the Event API
   *
   * @param string $event_name the name of the event
   * @param array $extra_params any extra data points to be included
   */
  public function log($event_name = 'Default', $extra_params = FALSE) {

    $event_metadata = [
      'plugin_version' => drupal_get_installed_schema_version('shareaholic'),
      'api_key' => $this->get_option('api_key'),
      'domain' => \Drupal::request()->getHost(),
      'language' => \Drupal::languageManager()->getCurrentLanguage()->getId(),
      'stats' => $this->getStats(),
      'diagnostics' => [
        'php_version' => PHP_VERSION,
        'drupal_version' => \Drupal::VERSION,
        'theme' => $this->themeManager->getActiveTheme(),
        'active_plugins' => $this->moduleHandler->getModuleList(),
      ],
      'features' => [
        //        'share_buttons' => $this->get_option('share_buttons'),
        //        'recommendations' => $this->get_option('recommendations'),
      ],
    ];

    if ($extra_params) {
      $event_metadata = array_merge($event_metadata, $extra_params);
    }

    $event_params = [
      'name' => "Drupal:" . $event_name,
      'data' => json_encode($event_metadata),
    ];

    $apiUrl = $this->shareaholicApi::API_URL . '/api/events';
    $settings = [
      'headers' => [
        'Content-type' => 'application/vnd.api+json',
      ],
      'body' => json_encode($event_params),
    ];

//    ::send($apiUrl, $settings, TRUE);

    // TODO doesn't seem to do much right now.

  }

  /**
   * Get the total number of comments for this site
   *
   * @return integer The total number of comments
   */
  public function totalComments() {
    if (!$this->connection->schema()->tableExists('comment')) {
      // TODO why it returns array if it declares integer?
      return array();
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
