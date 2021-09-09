<?php

namespace Drupal\clashofclans_location\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\clashofclans_api\Render;
/**
 * Returns responses for Clashofclans location routes.
 */
class ClashofclansLocationController extends ControllerBase {
  private $client;

  public function __construct(\Drupal\clashofclans_api\Client $client) {
      $this->client = $client;
  }

  public static function create(ContainerInterface $container) {
      $client = $container->get('clashofclans_api.client');
      return new static($client);
  }

  /**
   * Builds the Players response.
   */
  public function players(EntityInterface $clashofclans_location) {
    $build['content'] = $this->buildPlayers($clashofclans_location->id());
    return $build;
  }

  public function globalPlayers() {
    $build['content'] = $this->buildPlayers('global');
    return $build;
  }

  protected function buildPlayers($location_id) {
    $url = 'locations/'. $location_id. '/rankings/players';
    $data = $this->client->get($url);
    $fields = [
      'Rank' => 'rank',
      'League' => 'league',
      'Name'  => 'name',
      'exp'  => 'expLevel',
      'Clan'  => 'clan',
      'attack Wins'  => 'attackWins',
      'defense Wins' => 'defenseWins',
      'Trophies'  => 'trophies',
    ];

    if (isset($data['items'])) {
      $table = Render::players($data['items'], $fields);
      $table['#attributes']['class'][] = 'uk-table-middle';
      $table['#attributes']['class'][] = 'uk-table-small';
      $build['content'] = $table;
    } else {
      $build = ['#markup' => $this->t('No results.')];
    }
    return $build;
  }

  public function globalClans() {
    $url = 'locations/global/rankings/clans';
    $data = $this->client->get($url);
    if (isset($data['items'])) {
      $fields = [
        'Rank' => 'rank',
        'Badge' => 'badge',
        'Level'  => 'clanLevel',
        'Name'  => 'name',
        'members'  => 'members',
        'Location' => 'location',
        'Points'  => 'clanPoints',
      ];

      $table = Render::clans($data['items'], $fields);
      $table['#attributes']['class'][] = 'uk-table-middle';
      $table['#attributes']['class'][] = 'uk-table-small';
      $build['content'] = $table;
    } else {
      $build['content'] = ['#markup' => $this->t('No results.')];
    }

    return $build;
  }

  public function setTitle(EntityInterface $clashofclans_location) {
    return $clashofclans_location->getTitle();
  }

}
