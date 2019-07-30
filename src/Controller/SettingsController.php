<?php

namespace Drupal\shareaholic\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class SettingsController.
 */
class SettingsController extends ControllerBase {

  /**
   * Config Page.
   */
  public function configPage() {

    $path = drupal_get_path('module', 'shareaholic');

    $page_content['#markup'] = '<h1>TBD</h1>';

    $settings = \Drupal::config('shareaholic.settings');
    $api_key = $settings->get('api_key');

    // add tos window if required
    if (!$api_key) {
      $tos_content = [
        '#theme' => 'shareaholic_tos_modal',
        '#path' => '/' . $path . '/assets/img',
        '#attached' => [
          'library' => [
            'shareaholic/main',
            //          'shareaholic/tos-modal',
          ],
        ],
      ];

      $page_content['#markup'] .= \Drupal::service('renderer')
        ->render($tos_content);
    }

    return $page_content;

  }

  /**
   * Advanced Config Page.
   */
  public function advancedConfigPage() {

    $page = [];
    $page['#markup'] = 'advanced settings';

    return $page;

  }

}
