<?php

namespace Drupal\clashofclans_war;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of war type entities.
 *
 * @see \Drupal\clashofclans_war\Entity\ClashofclansWarType
 */
class ClashofclansWarTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['title'] = $this->t('Label');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['title'] = [
      'data' => $entity->label(),
      'class' => ['menu-label'],
    ];

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();

    $build['table']['#empty'] = $this->t(
      'No war types available. <a href=":link">Add war type</a>.',
      [':link' => Url::fromRoute('entity.clashofclans_war_type.add_form')->toString()]
    );

    return $build;
  }

}
