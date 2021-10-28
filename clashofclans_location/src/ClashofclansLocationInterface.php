<?php

namespace Drupal\clashofclans_location;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a location entity type.
 */
interface ClashofclansLocationInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the location title.
   *
   * @return string
   *   Title of the location.
   */
  public function getTitle();

  /**
   * Sets the location title.
   *
   * @param string $title
   *   The location title.
   *
   * @return \Drupal\clashofclans_location\ClashofclansLocationInterface
   *   The called location entity.
   */
  public function setTitle($title);

  /**
   * Gets the location creation timestamp.
   *
   * @return int
   *   Creation timestamp of the location.
   */
  public function getCreatedTime();

  /**
   * Sets the location creation timestamp.
   *
   * @param int $timestamp
   *   The location creation timestamp.
   *
   * @return \Drupal\clashofclans_location\ClashofclansLocationInterface
   *   The called location entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the location status.
   *
   * @return bool
   *   TRUE if the location is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets the location status.
   *
   * @param bool $status
   *   TRUE to enable this location, FALSE to disable.
   *
   * @return \Drupal\clashofclans_location\ClashofclansLocationInterface
   *   The called location entity.
   */
  public function setStatus($status);

}
