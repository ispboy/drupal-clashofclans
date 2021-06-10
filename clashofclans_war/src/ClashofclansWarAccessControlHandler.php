<?php

namespace Drupal\clashofclans_war;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the war entity type.
 */
class ClashofclansWarAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view war');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ['edit war', 'administer war'], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ['delete war', 'administer war'], 'OR');

      default:
        // No opinion.
        return AccessResult::neutral();
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions($account, ['create war', 'administer war'], 'OR');
  }

}
