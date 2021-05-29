<?php

namespace Drupal\clashofclans_clan;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the clan entity type.
 */
class ClashofclansClanAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view clan');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ['edit clan', 'administer clan'], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ['delete clan', 'administer clan'], 'OR');

      default:
        // No opinion.
        return AccessResult::neutral();
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions($account, ['create clan', 'administer clan'], 'OR');
  }

}
