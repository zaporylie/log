<?php

/**
 * @file
 * Contains \Drupal\log\LogAccessControlHandler.
 */

namespace Drupal\log;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the log log entity type.
 *
 * @see \Drupal\log\Entity\Log
 */
class LogAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view logs');
        break;

      case 'update':
        return AccessResult::allowedIfHasPermission($account, "edit any {$entity->bundle()} log");
        break;

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, "delete any {$entity->bundle()} log");
        break;

      default:
        // No opinion.
        return AccessResult::neutral();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, "create $entity_bundle log");
  }

  /**
   * {@inheritdoc}
   */
  protected function checkFieldAccess($operation, FieldDefinitionInterface $field_definition, AccountInterface $account, FieldItemListInterface $items = NULL) {
    // Only users with the administer nodes permission can edit administrative
    // fields.
    $administrative_fields = array('user_id', 'created');
    if ($operation == 'edit' && in_array($field_definition->getName(), $administrative_fields, TRUE)) {
      return AccessResult::allowedIfHasPermission($account, 'administer logs');
    }

    // No user can change read only fields.
    $read_only_fields = array('revision_timestamp', 'revision_uid');
    if ($operation == 'edit' && in_array($field_definition->getName(), $read_only_fields, TRUE)) {
      return AccessResult::forbidden();
    }

    return parent::checkFieldAccess($operation, $field_definition, $account, $items);
  }

}
