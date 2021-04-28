<?php

namespace Drupal\clashofclans_player\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "clashofclans_player_tag_form",
 *   admin_label = @Translation("Find a player"),
 *   category = @Translation("ClashOfClans")
 * )
 */
class TagFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\clashofclans_player\Form\TagForm');
    return $form;
  }

}
