<?php

namespace Drupal\shareaholic\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class AdvancedSettingsController.
 */
class AdvancedSettingsController extends ControllerBase {

  /**
   * Advanced Config Page.
   */
  public function advancedConfigPage() {

    $page = [];
    $page['#markup'] = 'advanced settings';

    return $page;

  }

}
