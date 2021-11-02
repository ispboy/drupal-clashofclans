<?php

namespace Drupal\clashofclans_leaguegroup;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a leaguegroup entity type.
 */
interface ClashofclansLeaguegroupInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the leaguegroup title.
   *
   * @return string
   *   Title of the leaguegroup.
   */
  public function getTitle();

  /**
   * Sets the leaguegroup title.
   *
   * @param string $title
   *   The leaguegroup title.
   *
   * @return \Drupal\clashofclans_leaguegroup\ClashofclansLeaguegroupInterface
   *   The called leaguegroup entity.
   */
  public function setTitle($title);

  /**
   * Gets the leaguegroup creation timestamp.
   *
   * @return int
   *   Creation timestamp of the leaguegroup.
   */
  public function getCreatedTime();

  /**
   * Sets the leaguegroup creation timestamp.
   *
   * @param int $timestamp
   *   The leaguegroup creation timestamp.
   *
   * @return \Drupal\clashofclans_leaguegroup\ClashofclansLeaguegroupInterface
   *   The called leaguegroup entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the leaguegroup status.
   *
   * @return bool
   *   TRUE if the leaguegroup is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets the leaguegroup status.
   *
   * @param bool $status
   *   TRUE to enable this leaguegroup, FALSE to disable.
   *
   * @return \Drupal\clashofclans_leaguegroup\ClashofclansLeaguegroupInterface
   *   The called leaguegroup entity.
   */
  public function setStatus($status);

}
