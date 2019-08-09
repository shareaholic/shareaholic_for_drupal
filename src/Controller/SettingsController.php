<?php

namespace Drupal\shareaholic\Controller;

use Drupal\Core\Config\Config;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Site\Settings;
use Drupal\shareaholic\Api\ShareaholicApi;
use Drupal\shareaholic\Helper\TOSManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class SettingsController.
 */
class SettingsController extends ControllerBase {


  /** @var Client */
  private $httpClient;

  /** @var ShareaholicApi */
  private $shareaholicApi;

  /** @var TOSManager */
  private $TOSManager;

  /** @var Config */
  private $shareaholicConfig;

  public function __construct(Client $httpClient, ShareaholicApi $shareaholicApi, TOSManager $TOSManager, Config $config)
  {
    $this->httpClient = $httpClient;
    $this->shareaholicApi = $shareaholicApi;
    $this->TOSManager = $TOSManager;
    $this->shareaholicConfig = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('shareaholic.api'),
      $container->get('shareaholic.tos_manager'),
      $container->get('shareaholic.editable_config')
    );
  }

  /**
   * Config Page.
   */
  public function configPage() {

    $path = drupal_get_path('module', 'shareaholic');
    $api_key = $this->shareaholicConfig->get('api_key');

    // add tos window if required
    if (!$api_key) {
      return [
        '#theme' => 'shareaholic_tos',
        '#path' => '/' . $path . '/assets/img',
        '#attached' => [
          'library' => [
            'shareaholic/main',
          ],
        ],
      ];
    }

    return [
      '#theme' => 'shareaholic_settings',
      '#apiKey' => $this->shareaholicConfig->get('api_key'),
      '#verificationKey' => $this->shareaholicApi->getPublisherToken(),
      '#apiHost' => $this->shareaholicApi::API_URL,
      '#serviceHost' => $this->shareaholicApi::SERVICE_URL,
      '#assetHost' => Settings::get('shareaholic_assets_host', 'https://cdn.shareaholic.net/'),
      '#language' => $this->languageManager()->getCurrentLanguage()->getId(),
    ];
  }

  /**
   * Advanced Config Page.
   */
  public function advancedConfigPage() {

    $page = [];
    $page['#markup'] = 'advanced settings';

    return $page;
  }

  public function generateKey() {

    $verification_key = md5(mt_rand());
    //$pageTypes = $this->pageTypes();

    $turned_on_recommendations_locations = $this->get_default_rec_on_locations();
    $turned_off_recommendations_locations = $this->get_default_rec_off_locations();
    $turned_on_share_buttons_locations = $this->get_default_sb_on_locations();
    $turned_off_share_buttons_locations = $this->get_default_sb_off_locations();

    $share_buttons_attributes = array_merge($turned_on_share_buttons_locations, $turned_off_share_buttons_locations);
    $recommendations_attributes = array_merge($turned_on_recommendations_locations, $turned_off_recommendations_locations);

    $post_data = [
      'configuration_publisher' => [
        'verification_key' => $verification_key,
        'site_name' => $this->config('system.site')->get('name'),
        'domain' => \Drupal::request()->getHost(),
        'platform_id' => '2',
        'language_id' => $this->shareaholicApi->getLanguageId($this->languageManager()->getCurrentLanguage()->getId()),
        'shortener' => 'shrlc',
        'recommendations_attributes' => [
          'locations_attributes' => $recommendations_attributes,
        ],
        'share_buttons_attributes' => [
          'locations_attributes' => $share_buttons_attributes,
        ],
      ],
    ];


    $client = $this->httpClient;
    $apiUrl = ShareaholicApi::KEY_GENERATING_URL;
    $settings = [
      'headers' => [
        'Content-type' => 'application/vnd.api+json',
      ],
      'body' => json_encode($post_data),
    ];

    $data = [];
    try {
      $response = $client->post($apiUrl, $settings);
      $data = (string) $response->getBody();

      if (empty($data)) {
        // TODO we should handle that gracefully.
        return FALSE;
      }

    } catch (RequestException $e) {

      // TODO we should handle that gracefully.
      return FALSE;

    }

    if (!empty($data)) {

      $json_response = json_decode($data, TRUE);


      $this->updateOptions([
        'version' => system_get_info('module', 'shareaholic')['version'],
        'api_key' => $json_response['api_key'],
        'verification_key' => $verification_key,
        // 'location_name_ids' => $json_response['location_name_ids'],
      ]);
    }

    if (isset($json_response['location_name_ids']) && is_array($json_response['location_name_ids']) && isset($json_response['location_name_ids']['recommendations']) && isset($json_response['location_name_ids']['share_buttons'])) {
      //      self::set_default_location_settings($json_response['location_name_ids']);
      //      ShareaholicContentManager::single_domain_worker();
    }
    else {
      //      ShareaholicUtilities::log_event('FailedToCreateApiKey', array('reason' => 'no location name ids the response was: ' . $response['data']));
    }

    $this->TOSManager->acceptTermsOfService();

    $url = \Drupal\Core\Url::fromRoute('shareaholic.settings')
      ->setAbsolute()
      ->toString();

    return new RedirectResponse($url);
  }

  /**
   * Get recommendations locations that should be turned on by default
   *
   * @return {Array}
   */
  public function get_default_rec_on_locations() {
    $page_types = node_type_get_names();
    $turned_on_recommendations_locations = [];

    foreach ($page_types as $key => $page_type_name) {

      if ($page_type_name === 'article' || $page_type_name === 'page') {
        $turned_on_recommendations_locations[] = [
          'name' => $page_type_name . '_below_content',
        ];
      }
    }

    return $turned_on_recommendations_locations;
  }

  /**
   * Get recommendations locations that should be turned off by default
   *
   * @return {Array}
   */
  public function get_default_rec_off_locations() {
    $page_types = node_type_get_names();
    $turned_off_recommendations_locations = [];

    foreach ($page_types as $key => $page_type_name) {
      if ($page_type_name !== 'article' && $page_type_name !== 'page') {
        $turned_off_recommendations_locations[] = [
          'name' => $page_type_name . '_below_content',
        ];
      }
    }

    return $turned_off_recommendations_locations;
  }

  /**
   * Get share buttons locations that should be turned on by default
   *
   * @return array
   */
  public function get_default_sb_on_locations() {
    $page_types = node_type_get_names();
    $turned_on_share_buttons_locations = [];

    foreach ($page_types as $key => $page_type_name) {
      $turned_on_share_buttons_locations[] = [
        'name' => $page_type_name . '_below_content',
      ];
    }

    return $turned_on_share_buttons_locations;
  }

  /**
   * Get share buttons locations that should be turned off by default
   *
   * @return array
   */
  public function get_default_sb_off_locations() {
    $page_types = node_type_get_names();
    $turned_off_share_buttons_locations = [];

    foreach ($page_types as $key => $page_type_name) {
      $turned_off_share_buttons_locations[] = [
        'name' => $page_type_name . '_above_content',
      ];
    }

    return $turned_off_share_buttons_locations;
  }

  /**
   * Update multiple keys of the settings object
   * Works like the Wordpress function for Shareaholic
   *
   * @param array $array an array of options to update
   *
   * @return bool
   */
  private function updateOptions($array) {
    foreach ($array as $key => $setting) {
      $this->shareaholicConfig->set($key, $setting)->save();
    }
  }
}
