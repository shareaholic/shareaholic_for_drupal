<?php

namespace Drupal\shareaholic\Helper;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\FieldConfigInterface;
use Drupal\node\NodeTypeInterface;

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
  public function enableShareaholic(NodeTypeInterface $nodeType) {
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
  public function disableShareaholic(NodeTypeInterface $nodeType) {
    $field = FieldConfig::loadByName('node', $nodeType->id(), 'shareaholic');

    if (empty($field)) {
      return;
    }
    $field->delete();

    field_purge_batch(10);

    $nodeType->unsetThirdPartySetting('shareaholic', 'locations_share_buttons');
    $nodeType->unsetThirdPartySetting('shareaholic', 'locations_recommendations');
    $nodeType->save();
  }

  /**
   * @param NodeTypeInterface $nodeType
   * @return bool
   */
  public function isShareaholicEnabled(NodeTypeInterface $nodeType): bool {
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
  public function getShareaholicEnabledNodeTypes(): array {
    $nodeTypes = $this->nodeTypeStorage->loadMultiple();

    $result = [];
    foreach ($nodeTypes as $nodeType) {
      if ($this->isShareaholicEnabled($nodeType)) $result[] = $nodeType;
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
    return $nodeType->getThirdPartySetting('shareaholic', "locations_$locationType", []);
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
   * @param $nodeTypeId
   * @param $name
   *
   * @return string
   */
  private function createLocationName($nodeTypeId, $name) {
    return "{$nodeTypeId}_${name}";
  }
}
