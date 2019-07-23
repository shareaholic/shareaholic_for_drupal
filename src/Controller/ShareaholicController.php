<?php

namespace Drupal\shareaholic\Controller;

use Drupal\Core\Controller\ControllerBase;

class ShareaholicController extends ControllerBase {

  public function configPage() {

    $page = [];

    $page['#markup'] = '';

    return $page;

  }

  public function generateKey() {


    $verification_key = md5(mt_rand());
    //    $page_types = self::page_types();
    //    $turned_on_recommendations_locations = self::get_default_rec_on_locations();
    //    $turned_off_recommendations_locations = self::get_default_rec_off_locations();
    //    $turned_on_share_buttons_locations = self::get_default_sb_on_locations();
    //    $turned_off_share_buttons_locations = self::get_default_sb_off_locations

    //    $share_buttons_attributes = array_merge($turned_on_share_buttons_locations, $turned_off_share_buttons_locations);
    //    $recommendations_attributes = array_merge($turned_on_recommendations_locations, $turned_off_recommendations_locations);

    $post_data = [
      'configuration_publisher' => [
        'verification_key' => $verification_key,
        //        'site_name' => self::site_name(),
        //        'domain' => self::site_url(),
        'platform_id' => '2',
        //        'language_id' => self::site_language(),
        'shortener' => 'shrlc',
        //        'recommendations_attributes' => [
        //          //          'locations_attributes' => $recommendations_attributes
        //        ],
        //        'share_buttons_attributes' => [
        //          //          'locations_attributes' => $share_buttons_attributes
        //        ],
      ],
    ];

    $apiUrl = 'http://spreadaholic.com:8080';
    $settings = [
      'method' => 'POST',
      'headers' => array(
        'Content-Type' => 'application/json'
      ),
      'data' => json_encode($post_data)
    ];

    try {
      $response = \Drupal::httpClient()->get($apiUrl, $settings);
      $data = (string) $response->getBody();
      if (empty($data)) {
        return FALSE;
      }
    } catch (RequestException $e) {
      return FALSE;
    }


    $page = [];

    $page['#markup'] = 'a' . $verification_key;

    return $page;

  }

}
