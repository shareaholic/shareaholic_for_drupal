<?php

namespace Drupal\shareaholic\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\shareaholic\Logger\EventLogger;

/**
 * Class TOSManager
 */
class TOSManager {

  /** @var ConfigFactoryInterface */
  private $configFactory;

  /** @var EventLogger */
  private $eventLogger;

  public function __construct(ConfigFactoryInterface $configFactory, EventLogger $eventLogger)
  {
    $this->configFactory = $configFactory;
    $this->eventLogger = $eventLogger;
  }

  /**
   * Returns whether the user has accepted our terms of service.
   * If the user has accepted, return true otherwise return NULL
   *
   * @return mixed (true or NULL)
   */
  public function hasAcceptedTermsOfService() {
    $this->configFactory->get('shareaholic_has_accepted_tos');
  }

  /**
   * Accepts the terms of service by setting the variable to true
   */
  public function acceptTermsOfService() {
    $settings = $this->configFactory->getEditable('shareaholic.settings');
    $settings->set('shareaholic_has_accepted_tos', TRUE);

    $this->eventLogger->log('AcceptedToS');
  }
}
