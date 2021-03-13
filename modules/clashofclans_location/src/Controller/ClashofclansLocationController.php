<?php

namespace Drupal\clashofclans_location\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use ClashOfClans\Client;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Returns responses for Clashofclans location routes.
 */
class ClashofclansLocationController extends ControllerBase {

  private $client;

  public function __construct() {
    $key = \Drupal::config('clashofclans.settings')->get('key');
    $this->client = new Client($key);
  }
  /**
   * Builds the response.
   */
  public function clans(EntityInterface $clashofclans_location) {

    $client = $this->client;
    $rows = [];

    try {
      $rankings = $client->getRankingsForLocation($clashofclans_location->id(), 'clans'); // returns array of Clan objects

      foreach($rankings as $key => $ranking) {
        $tag = $ranking->tag();
        $name = Link::fromTextAndUrl($ranking->name(), Url::fromUri('internal:/reports/search/'. $tag))->toString();
        $rows[] = [$ranking->rank(), $ranking->previousRank(), $name, $ranking->clanLevel(), $ranking->members(), $ranking->clanPoints()];
      }
    }
    catch (RequestException $error) {
      $logger = \Drupal::logger('ClashOfClans Client error');
      $logger->error($error->getMessage());
    }

    $header = ['rank', 'previousRank', 'name', 'clanLevel', 'members','clanPoints'];
    $build['content'] = [
      '#type' => 'table',
      '#sticky' => TRUE,
      '#header' => $header,
      '#rows' => $rows,
    ];

    return $build;
  }

  /**
   * Builds the response.
   */
  public function players(EntityInterface $clashofclans_location) {

    $client = $this->client;
    $rows = [];

    try {
      $rankings = $client->getRankingsForLocation($clashofclans_location->id(), 'players'); // returns array of Clan objects
      // $first = current($rankings);
      // ksm($clashofclans_location->getTitle());

      foreach($rankings as $key => $ranking) {
        $rows[] = [
          $ranking->rank(),
          $ranking->previousRank(),
          $ranking->name(),
          $ranking->expLevel(),
          $ranking->trophies(),
          $ranking->attackWins(),
          $ranking->defenseWins(),
        ];
      }
    }
    catch (RequestException $error) {
      $logger = \Drupal::logger('ClashOfClans Client error');
      $logger->error($error->getMessage());
    }

    $header = ['rank', 'previousRank', 'name', 'expLevel', 'trophies','attackWins', 'defenseWins'];
    $build['content'] = [
      '#type' => 'table',
      '#sticky' => TRUE,
      '#header' => $header,
      '#rows' => $rows,
    ];

    return $build;
  }

  public function setTitle(EntityInterface $clashofclans_location) {
    return $clashofclans_location->getTitle();
  }

}
