<?php

namespace Drupal\clashofclans_leaguegroup;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the leaguegroup entity type.
 */
class LeaguegroupAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view leaguegroup');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ['edit leaguegroup', 'administer leaguegroup'], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ['delete leaguegroup', 'administer leaguegroup'], 'OR');

      default:
        // No opinion.
        return AccessResult::neutral();
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions($account, ['create leaguegroup', 'administer leaguegroup'], 'OR');
  }

}
