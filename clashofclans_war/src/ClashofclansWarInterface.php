<?php

namespace Drupal\clashofclans_war;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a war entity type.
 */
interface ClashofclansWarInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the war title.
   *
   * @return string
   *   Title of the war.
   */
  public function getTitle();

  /**
   * Sets the war title.
   *
   * @param string $title
   *   The war title.
   *
   * @return \Drupal\clashofclans_war\ClashofclansWarInterface
   *   The called war entity.
   */
  public function setTitle($title);

  /**
   * Gets the war creation timestamp.
   *
   * @return int
   *   Creation timestamp of the war.
   */
  public function getCreatedTime();

  /**
   * Sets the war creation timestamp.
   *
   * @param int $timestamp
   *   The war creation timestamp.
   *
   * @return \Drupal\clashofclans_war\ClashofclansWarInterface
   *   The called war entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the war status.
   *
   * @return bool
   *   TRUE if the war is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets the war status.
   *
   * @param bool $status
   *   TRUE to enable this war, FALSE to disable.
   *
   * @return \Drupal\clashofclans_war\ClashofclansWarInterface
   *   The called war entity.
   */
  public function setStatus($status);

}
