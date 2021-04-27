<?php

namespace Drupal\clashofclans_player;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a player entity type.
 */
interface ClashofclansPlayerInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the player title.
   *
   * @return string
   *   Title of the player.
   */
  public function getTitle();

  /**
   * Sets the player title.
   *
   * @param string $title
   *   The player title.
   *
   * @return \Drupal\clashofclans_player\ClashofclansPlayerInterface
   *   The called player entity.
   */
  public function setTitle($title);

  /**
   * Gets the player creation timestamp.
   *
   * @return int
   *   Creation timestamp of the player.
   */
  public function getCreatedTime();

  /**
   * Sets the player creation timestamp.
   *
   * @param int $timestamp
   *   The player creation timestamp.
   *
   * @return \Drupal\clashofclans_player\ClashofclansPlayerInterface
   *   The called player entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the player status.
   *
   * @return bool
   *   TRUE if the player is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets the player status.
   *
   * @param bool $status
   *   TRUE to enable this player, FALSE to disable.
   *
   * @return \Drupal\clashofclans_player\ClashofclansPlayerInterface
   *   The called player entity.
   */
  public function setStatus($status);

}
