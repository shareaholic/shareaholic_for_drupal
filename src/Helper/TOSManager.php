<?php

namespace Drupal\shareaholic\Helper;

use Drupal\Core\Config\Config;
use Drupal\shareaholic\Logger\EventLogger;

/**
 * Class TOSManager
 */
class TOSManager {

  /** @var Config */
  private $shareaholicConfig;

  /** @var EventLogger */
  private $eventLogger;

  public function __construct(Config $shareaholicConfig, EventLogger $eventLogger)
  {
    $this->shareaholicConfig = $shareaholicConfig;
    $this->eventLogger = $eventLogger;
  }

  /**
   * Returns whether the user has accepted our terms of service.
   * If the user has accepted, return true otherwise return NULL
   *
   * @return mixed (true or NULL)
   */
  public function hasAcceptedTermsOfService() {
    return $this->shareaholicConfig->get('has_accepted_tos');
  }

  /**
   * Accepts the terms of service by setting the variable to true
   */
  public function acceptTermsOfService() {
    $this->shareaholicConfig->set('has_accepted_tos', TRUE);

    $this->eventLogger->log('AcceptedToS');
  }
}
