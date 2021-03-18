<?php

namespace Drupal\clashofclans_clan\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\clashofclans_clan\Controller\ClashofclansClanController;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "clashofclans_clan_global_ranking",
 *   admin_label = @Translation("Global Ranking"),
 *   category = @Translation("ClashOfClans Clan")
 * )
 */
class GlobalRanking extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // $build['content'] = [
    //   '#markup' => $this->t('It works!'),
    // ];
    // return $build;
    return (new ClashofclansClanController)->global();
  }

}
