<?php

namespace Drupal\clashofclans_clan\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "clashofclans_clan_tag_form",
 *   admin_label = @Translation("Find a clan"),
 *   category = @Translation("ClashOfClans")
 * )
 */
class TagFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\clashofclans_clan\Form\TagForm');
    return $form;
  }

}
