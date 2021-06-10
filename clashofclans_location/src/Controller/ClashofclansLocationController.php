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
      'expLevel'  => 'expLevel',
      'Clan'  => 'clan',
      'attackWins'  => 'attackWins',
      'defenseWins' => 'defenseWins',
      'trophies'  => 'trophies',
    ];

    $build = Render::players($data['items'], $fields);
    return $build;
  }

  public function globalClans() {
    $url = 'locations/global/rankings/clans';
    $data = $this->client->get($url);
    $fields = [
      'Rank' => 'rank',
      'Badge' => 'badge',
      'Name'  => 'name',
      'clanLevel'  => 'clanLevel',
      'members'  => 'members',
      'Location' => 'location',
      'clanPoints'  => 'clanPoints',
    ];

    $build['content'] = Render::clans($data['items'], $fields);

    return $build;
  }

  public function setTitle(EntityInterface $clashofclans_location) {
    return $clashofclans_location->getTitle();
  }

}
