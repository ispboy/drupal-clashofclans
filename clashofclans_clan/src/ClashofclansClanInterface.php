<?php

namespace Drupal\clashofclans_clan;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a clan entity type.
 */
interface ClashofclansClanInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the clan title.
   *
   * @return string
   *   Title of the clan.
   */
  public function getTitle();

  /**
   * Sets the clan title.
   *
   * @param string $title
   *   The clan title.
   *
   * @return \Drupal\clashofclans_clan\ClashofclansClanInterface
   *   The called clan entity.
   */
  public function setTitle($title);

  /**
   * Gets the clan creation timestamp.
   *
   * @return int
   *   Creation timestamp of the clan.
   */
  public function getCreatedTime();

  /**
   * Sets the clan creation timestamp.
   *
   * @param int $timestamp
   *   The clan creation timestamp.
   *
   * @return \Drupal\clashofclans_clan\ClashofclansClanInterface
   *   The called clan entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the clan status.
   *
   * @return bool
   *   TRUE if the clan is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets the clan status.
   *
   * @param bool $status
   *   TRUE to enable this clan, FALSE to disable.
   *
   * @return \Drupal\clashofclans_clan\ClashofclansClanInterface
   *   The called clan entity.
   */
  public function setStatus($status);

}
