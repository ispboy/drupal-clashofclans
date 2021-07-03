<?php

namespace Drupal\clashofclans_player;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the player entity type.
 */
class ClashofclansPlayerAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view player');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ['edit player', 'administer player'], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ['delete player', 'administer player'], 'OR');

      default:
        // No opinion.
        return AccessResult::neutral();
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions($account, ['create player', 'administer player'], 'OR');
  }

}
