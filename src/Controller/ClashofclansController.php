<?php

namespace Drupal\clashofclans\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\clashofclans\ClashofclansCore;

/**
 * Returns responses for ClashOfClans routes.
 */
class ClashofclansController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {
$clan = ClashofclansCore::getClan('#C00RJP');
$clan2 = ClashofclansCore::getClan('#PQP8UJCQ');
    // $clan = $client->getClan('#C00RJP'); // returns Clan object
    $clan->name(); // "Hattrickers"
    $clan->level(); // 8
    $clan->warWins(); // 168
    $leader = $clan->memberList()->coleaders();
    ksm($clan->name(), $clan2->name());

    // $leagues = $client->getLeagues();
    // foreach ($leagues as $key => $league) {
    //   ksm($league->name(), $league->id(), $league->icon()->small());
    // }

    // $locations = $client->getLocations();
    // $locationId = 32000029;
    // $rankings = $client->getRankingsForLocation($locationId, 'clans'); // returns array of Clan objects
    // foreach($rankings as $key => $clan) {
    //   ksm($clan->name(), $clan->clanLevel(), $clan->clanPoints(), $clan->rank());
    // }


    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
