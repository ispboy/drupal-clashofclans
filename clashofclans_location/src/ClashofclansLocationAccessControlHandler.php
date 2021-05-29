<?php

namespace Drupal\clashofclans_location;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the location entity type.
 */
class ClashofclansLocationAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view location');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ['edit location', 'administer location'], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ['delete location', 'administer location'], 'OR');

      default:
        // No opinion.
        return AccessResult::neutral();
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions($account, ['create location', 'administer location'], 'OR');
  }

}
