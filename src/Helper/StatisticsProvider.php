<?php

namespace Drupal\shareaholic\Helper;

use Drupal\Core\Database\Connection;

class StatisticsProvider
{
  /** @var Connection */
  private $connection;

  public function __construct(Connection $connection)
  {
    $this->connection = $connection;
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

  /**
   * Get the total number of comments for this site
   *
   * @return integer
   *   The total number of comments
   */
  private function totalComments() {
    if (!$this->connection->schema()->tableExists('comment')) {
      return 0;
    }

    return $this->connection->query("SELECT count(cid) FROM {comment}")->fetchField();
  }
}
