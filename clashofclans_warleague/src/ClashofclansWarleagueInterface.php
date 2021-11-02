<?php

namespace Drupal\clashofclans_warleague;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a warleague entity type.
 */
interface ClashofclansWarleagueInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the warleague title.
   *
   * @return string
   *   Title of the warleague.
   */
  public function getTitle();

  /**
   * Sets the warleague title.
   *
   * @param string $title
   *   The warleague title.
   *
   * @return \Drupal\clashofclans_warleague\ClashofclansWarleagueInterface
   *   The called warleague entity.
   */
  public function setTitle($title);

  /**
   * Gets the warleague creation timestamp.
   *
   * @return int
   *   Creation timestamp of the warleague.
   */
  public function getCreatedTime();

  /**
   * Sets the warleague creation timestamp.
   *
   * @param int $timestamp
   *   The warleague creation timestamp.
   *
   * @return \Drupal\clashofclans_warleague\ClashofclansWarleagueInterface
   *   The called warleague entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the warleague status.
   *
   * @return bool
   *   TRUE if the warleague is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets the warleague status.
   *
   * @param bool $status
   *   TRUE to enable this warleague, FALSE to disable.
   *
   * @return \Drupal\clashofclans_warleague\ClashofclansWarleagueInterface
   *   The called warleague entity.
   */
  public function setStatus($status);

}
