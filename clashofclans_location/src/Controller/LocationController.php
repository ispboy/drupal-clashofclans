<?php

namespace Drupal\clashofclans_location\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_api\GuzzleCache;
use Drupal\Core\Entity\EntityInterface;

/**
 * Returns responses for Clashofclans location routes.
 */
class LocationController extends ControllerBase {
  private $client;

  public function __construct(GuzzleCache $client) {
      $this->client = $client;
  }

  public static function create(ContainerInterface $container) {
      $client = $container->get('clashofclans_api.guzzle_cache');
      return new static($client);
  }

  /**
   * Builds the Players response.
   */
  public function players(EntityInterface $clashofclans_location) {
    return $this->buildPlayers($clashofclans_location->id());
  }

  public function globalPlayers() {
    return $this->buildPlayers('global');
  }

  protected function buildPlayers($location_id) {
    $url = 'locations/'. $location_id. '/rankings/players';
    $data = $this->client->getData($url);
    if (isset($data['items'])) {
      $items = $data['items'];

      $header = [
        'rank' => ['data' => '#'],
        // 'previousRank' => ['data' => 'Prev', 'class' => [RESPONSIVE_PRIORITY_LOW]],
        'league' => ['data' => 'League', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        'name' => ['data' => 'Name'],
        'tag' => ['data' => 'Tag', 'class' => [RESPONSIVE_PRIORITY_LOW]],
        'expLevel' => ['data' => 'exp', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        'clan' => ['data' => 'clan', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        'attackWins' => ['data' => 'attackWins', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        'defenseWins' => ['data' => 'defenseWins', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        'trophies' => ['data' => 'Trophies'],
      ];

      $build['content'] = \Drupal\clashofclans_api\Render::table($header, $items);
      $build['content']['#cache']['max-age'] = $this->client->getMaxAge();

    } else {
      $build['content'] = ['#markup' => $this->t('No results.')];
    }

    return $build;
  }

  /**
   * Builds the Players response.
   */
  public function clans(EntityInterface $clashofclans_location) {
    return $this->buildClans($clashofclans_location->id());
  }

  public function globalClans() {
    return $this->buildClans('global');
  }

  public function buildClans($location_id) {
    $url = 'locations/'. $location_id. '/rankings/clans';
    $data = $this->client->getData($url);
    if (isset($data['items'])) {
      $items = $data['items'];

      $header = [
        'rank' => ['data' => '#'],
        'badgeUrls' => ['data' => 'badge', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        'clanLevel' => ['data' => 'clanLevel', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        'name' => ['data' => 'Name'],
        'tag' => ['data' => 'Tag', 'class' => [RESPONSIVE_PRIORITY_LOW]],
        'location' => ['data' => 'location', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        'members' => ['data' => 'members', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        'clanPoints' => ['data' => 'Points'],
      ];

      if ($location_id !== 'global') unset($header['location']);

      $build['content'] = \Drupal\clashofclans_api\Render::table($header, $items);
      $build['content']['#cache']['max-age'] = $this->client->getMaxAge();

    } else {
      $build['content'] = ['#markup' => $this->t('No results.')];
    }

    return $build;
  }

  public function setTitle(EntityInterface $clashofclans_location, $custom_arg) {
    $location = $clashofclans_location->getTitle();
    return $this->t('Top '. $custom_arg. ' in @location', ['@location' => $location], ['context' => 'clashofclans_location']);
  }

}
