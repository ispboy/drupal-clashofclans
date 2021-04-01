<?php

namespace Drupal\clashofclans\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for ClashOfClans routes.
 */
class ClashofclansController extends ControllerBase {
  private $client;

  public function __construct(\Drupal\clashofclans\ClashofclansClient $client)
  {
      $this->client = $client;
  }

  public static function create(ContainerInterface $container)
  {
      $client = $container->get('clashofclans.client');
      return new static($client);
  }
  /**
   * Builds the response.
   */
  public function build() {
    $client = $this->client;
    $tag = '#P9RJUCR2U';
    $player = $this->client->get('getPlayer', ['tag' => $tag]);
    // $clan = $client->getClan('#C00RJP'); // returns Clan object
    ksm($player->legendStatistics());

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
