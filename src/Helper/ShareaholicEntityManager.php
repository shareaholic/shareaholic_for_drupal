<?php

namespace Drupal\shareaholic\Helper;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\FieldConfigInterface;
use Drupal\node\NodeInterface;
use Drupal\node\NodeTypeInterface;
use Drupal\shareaholic\Api\ShareaholicApi;
use Drupal\shareaholic\Plugin\Field\FieldType\ShareaholicFieldType;

/**
 * Class ShareaholicEntityManager
 */
class ShareaholicEntityManager {

  /** @var EntityFieldManagerInterface */
  private $entityFieldManager;

  /** @var EntityStorageInterface */
  private $nodeTypeStorage;

  public function __construct(EntityTypeManagerInterface $entityTypeManager, EntityFieldManagerInterface $entityFieldManager) {
    $this->nodeTypeStorage = $entityTypeManager->getStorage('node_type');
    $this->entityFieldManager = $entityFieldManager;
  }


  /**
   * @param \Drupal\node\NodeTypeInterface $nodeType
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function enableContentSettings(NodeTypeInterface $nodeType) {
    $fieldStorage = FieldStorageConfig::loadByName('node', 'shareaholic');

    if (!$fieldStorage) {
      $fieldStorage = FieldStorageConfig::create([
        'entity_type' => 'node',
        'field_name' => 'shareaholic',
        'type' => 'shareaholic_content_settings',
        'locked' => TRUE,
      ]);
      $fieldStorage->save();
    }

    $field = FieldConfig::create([
      'field_storage' => $fieldStorage,
      'bundle' => $nodeType->id(),
      'label' => 'Shareaholic Content Settings',
      'field_name' => 'shareaholic',
      'entity_type' => 'node',
    ]);
    $field->save();

    $entityFormDisplay = EntityFormDisplay::load('node.' . $nodeType->id() . '.default');
    if (!$entityFormDisplay) {
      $entityFormDisplay = EntityFormDisplay::create([
        'targetEntityType' => 'node',
        'bundle' => $nodeType->id(),
        'mode' => 'default',
        'status' => TRUE,
      ]);
    }
    $entityFormDisplay->setComponent('shareaholic', [
      'type' => 'shareaholic_widget_type',
    ]);
    $entityFormDisplay->save();

    $nodeType->setThirdPartySetting('shareaholic', 'locations_share_buttons', [$this->createLocationName($nodeType->id(), 'default')]);
    $nodeType->setThirdPartySetting('shareaholic', 'locations_recommendations', [$this->createLocationName($nodeType->id(), 'default')]);
    $nodeType->save();
  }

  /**
   * @param \Drupal\node\NodeTypeInterface $nodeType
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function disableContentSettings(NodeTypeInterface $nodeType) {
    $field = FieldConfig::loadByName('node', $nodeType->id(), 'shareaholic');

    if (empty($field)) {
      return;
    }
    $field->delete();

    field_purge_batch(10);
  }

  /**
   * @param NodeTypeInterface $nodeType
   * @return bool
   */
  public function areContentSettingsEnabled(NodeTypeInterface $nodeType): bool {
    $fieldDefinitions = $this->entityFieldManager->getFieldDefinitions('node', $nodeType->id());

    $shareaholicFields = array_filter($fieldDefinitions, static function($fieldDefinition) {
      return $fieldDefinition instanceof FieldConfigInterface && $fieldDefinition->getName() === 'shareaholic' && $fieldDefinition->getType() === 'shareaholic_content_settings';
    });

    if (count($shareaholicFields) > 1) {
      throw new \LogicException("Error. Too many shareaholic fields attached to the content type.");
    }

    return !empty($shareaholicFields);
  }

  /**
   * @return NodeTypeInterface[]
   */
  public function getContentTypesWithContentSettings(): array {
    $nodeTypes = $this->nodeTypeStorage->loadMultiple();

    $result = [];
    foreach ($nodeTypes as $nodeType) {
      if ($this->areContentSettingsEnabled($nodeType)) $result[] = $nodeType;
    }

    return $result;
  }

  /**
   * Extract locations from the node type.
   *
   * @param string $locationType
   * @param \Drupal\node\NodeTypeInterface $nodeType
   * @return array
   */
  public function extractLocations($locationType, NodeTypeInterface $nodeType): array {
    $locations = $nodeType->getThirdPartySetting('shareaholic', "locations_$locationType", []);

    // There should aways be the default location.
    $defaultLocation = self::createLocationName($nodeType->id(), 'default');
    if (!in_array($defaultLocation, $locations, TRUE)) {
      array_unshift($locations , $defaultLocation);
    }

    return $locations;
  }


  /**
   * @param $locationType
   *
   * @return array
   */
  public function getAllLocations($locationType): array {
    $nodeTypes = $this->nodeTypeStorage->loadMultiple();

    $locations = [];
    foreach ($nodeTypes as $nodeType) {
      $locations = array_merge($locations, $this->extractLocations($locationType, $nodeType));
    }

    return $locations;
  }

  /**
   * @param $location
   * @param $locationType
   * @param \Drupal\node\NodeTypeInterface $nodeType
   *
   * @return bool
   */
  public function hasLocation($location, $locationType, NodeTypeInterface $nodeType): bool {
    $locations = $this->extractLocations($locationType, $nodeType);

    return in_array($location, $locations, TRUE);
  }

  /**
   * @param string $locationName
   * @param string $locationType
   * @param \Drupal\node\NodeTypeInterface $nodeType
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addLocation($locationName, $locationType, NodeTypeInterface $nodeType) {
    $locations = $nodeType->getThirdPartySetting('shareaholic', "locations_$locationType", []);
    $locations[] = $this->createLocationName($nodeType->id(), $locationName);

    $nodeType->setThirdPartySetting('shareaholic', "locations_$locationType", $locations);
    $nodeType->save();
  }

  /**
   * @param $locationName
   * @param $locationType
   * @param \Drupal\node\NodeTypeInterface $nodeType
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function removeLocation($locationName, $locationType, NodeTypeInterface $nodeType) {
    $locations = $nodeType->getThirdPartySetting('shareaholic', "locations_$locationType", []);

    if (($key = array_search($locationName, $locations, TRUE)) !== FALSE) {
        unset($locations[$key]);
    }

    $nodeType->setThirdPartySetting('shareaholic', "locations_$locationType", $locations);
    $nodeType->save();
  }

  /**
   * @param \Drupal\node\NodeInterface $node
   *
   * @return array
   */
  public function getContentSettings(NodeInterface $node): array {

    $nodeType = $this->nodeTypeStorage->load($node->getType());
    if (!$this->areContentSettingsEnabled($nodeType)) {
      return ShareaholicFieldType::getDefaultContentSettings();
    }

    return !empty($node->get('shareaholic')->getValue()) ? $node->get('shareaholic')->getValue()[0] : ShareaholicFieldType::getDefaultContentSettings();
  }

  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function removeAllLocations() {
    $nodeTypes = $this->nodeTypeStorage->loadMultiple();
    /** @var \Drupal\node\NodeTypeInterface $nodeType */
    foreach ($nodeTypes as $nodeType) {
      $nodeType->unsetThirdPartySetting('shareaholic', 'locations_share_buttons');
      $nodeType->unsetThirdPartySetting('shareaholic', 'locations_recommendations');
      $nodeType->save();
    }
  }

  /**
   * @param $nodeTypeId
   * @param $name
   *
   * @return string
   */
  public static function createLocationName($nodeTypeId, $name) {
    return "{$nodeTypeId}_${name}";
  }
}
