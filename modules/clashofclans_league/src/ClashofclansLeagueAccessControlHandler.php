<?php

namespace Drupal\clashofclans_league;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the league entity type.
 */
class ClashofclansLeagueAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view league');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ['edit league', 'administer league'], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ['delete league', 'administer league'], 'OR');

      default:
        // No opinion.
        return AccessResult::neutral();
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions($account, ['create league', 'administer league'], 'OR');
  }

}
