<?php

namespace Drupal\shareaholic\Helper;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\field\FieldConfigInterface;
use Drupal\node\NodeTypeInterface;

/**
 * Class ShareaholicEntityManager
 */
class ShareaholicEntityManager {

  /** @var EntityFieldManagerInterface */
  private $entityFieldManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager, EntityFieldManagerInterface $entityFieldManager) {
    $this->nodeTypeStorage = $entityTypeManager->getStorage('node_type');
    $this->entityFieldManager = $entityFieldManager;
  }


  public function enableShareaholic(NodeTypeInterface $nodeType) {
    $fieldDefinitions = $this->entityFieldManager->getFieldDefinitions('node', $nodeType);
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
}
