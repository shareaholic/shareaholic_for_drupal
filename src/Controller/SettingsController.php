<?php

namespace Drupal\shareaholic\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
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

  /** @var RendererInterface */
  private $renderer;


  public function __construct(Client $httpClient, RendererInterface $renderer, ShareaholicApi $shareaholicApi, TOSManager $TOSManager)
  {
    $this->httpClient = $httpClient;
    $this->shareaholicApi = $shareaholicApi;
    $this->TOSManager = $TOSManager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('renderer'),
      $container->get('shareaholic.api'),
      $container->get('shareaholic.tos_manager')
    );
  }

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
          ],
        ],
      ];

      $page_content['#markup'] .= $this->renderer->render($tos_content);
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
        'site_name' => $this->config('name'),
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

    $settings = $this->configFactory->getEditable('shareaholic.settings');
    foreach ($array as $key => $setting) {
      $settings->set($key, $setting)->save();
    }
  }
}
