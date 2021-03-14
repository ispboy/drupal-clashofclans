<?php

namespace Drupal\clashofclans_clan\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "clashofclans_clan_example",
 *   admin_label = @Translation("Example"),
 *   category = @Translation("ClashOfClans Clan")
 * )
 */
class ExampleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [
      '#markup' => $this->t('It works!'),
    ];
    return $build;
  }

}
