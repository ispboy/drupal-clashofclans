<?php

namespace Drupal\clashofclans_warleague;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the warleague entity type.
 */
class ClashofclansWarleagueAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view warleague');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ['edit warleague', 'administer warleague'], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ['delete warleague', 'administer warleague'], 'OR');

      default:
        // No opinion.
        return AccessResult::neutral();
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions($account, ['create warleague', 'administer warleague'], 'OR');
  }

}
