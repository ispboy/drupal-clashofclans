<?php

namespace Drupal\clashofclans_league;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a league entity type.
 */
interface ClashofclansLeagueInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the league title.
   *
   * @return string
   *   Title of the league.
   */
  public function getTitle();

  /**
   * Sets the league title.
   *
   * @param string $title
   *   The league title.
   *
   * @return \Drupal\clashofclans_league\ClashofclansLeagueInterface
   *   The called league entity.
   */
  public function setTitle($title);

  /**
   * Gets the league creation timestamp.
   *
   * @return int
   *   Creation timestamp of the league.
   */
  public function getCreatedTime();

  /**
   * Sets the league creation timestamp.
   *
   * @param int $timestamp
   *   The league creation timestamp.
   *
   * @return \Drupal\clashofclans_league\ClashofclansLeagueInterface
   *   The called league entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the league status.
   *
   * @return bool
   *   TRUE if the league is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets the league status.
   *
   * @param bool $status
   *   TRUE to enable this league, FALSE to disable.
   *
   * @return \Drupal\clashofclans_league\ClashofclansLeagueInterface
   *   The called league entity.
   */
  public function setStatus($status);

}
