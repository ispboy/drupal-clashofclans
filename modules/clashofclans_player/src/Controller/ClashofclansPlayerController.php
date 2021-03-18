<?php

namespace Drupal\clashofclans_player\Controller;

use Drupal\Core\Controller\ControllerBase;
use ClashOfClans\Client;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Returns responses for ClashOfClans Player routes.
 */
class ClashofclansPlayerController extends ControllerBase {
  private $client;

  public function __construct() {
    $key = \Drupal::config('clashofclans.settings')->get('key');
    $this->client = new Client($key);
  }

  /**
   * Builds the response.
   */
  public function global() {
    $client = $this->client;
    try {
      $rankings = $client->getRankingsForLocation('global', 'players'); // returns array of player objects
      foreach($rankings as $key => $ranking) {
        $clan = '';
        if ($ranking->clan()) {
          $clan = Link::fromTextAndUrl($ranking->clan()->name(), Url::fromRoute('clashofclans_clan.tag', ['tag' => $ranking->clan()->tag()]))->toString();
        }
        $tag = $ranking->tag();
        $name = Link::fromTextAndUrl($ranking->name(), Url::fromRoute('clashofclans_player.tag', ['tag' => $tag]))->toString();
        $league = [
          '#theme' => 'image',
          '#uri' => $ranking->league()->iconUrls()->tiny(),
          '#width' => 36,
          '#height' => 36,
        ];
        $rows[] = [
          $ranking->rank(),
          $ranking->previousRank(),
          // \Drupal::service('renderer')->render($badge),
          $name,
          $clan,
          \Drupal::service('renderer')->render($league),
          $ranking->expLevel(),
          $ranking->attackWins(),
          $ranking->defenseWins(),
          $ranking->trophies(),
        ];
      }
    }
    catch (RequestException $error) {
      $logger = \Drupal::logger('ClashOfClans Client error');
      $logger->error($error->getMessage());
    }

    $header = ['rank', 'previousRank', 'name', 'clan', 'league', 'expLevel', 'attackWins', 'defenseWins', 'trophies'];
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
  public function tag($tag) {
    $client = $this->client;

    try {

      $player = $client->getPlayer($tag);
      // $badge = [
      //   '#theme' => 'image',
      //   '#uri' => $clan->badgeUrls()->small(),
      // ];
      $clan = '';
      if ($player->clan()) {
        $clan = Link::fromTextAndUrl($player->clan()->name(), Url::fromRoute('clashofclans_clan.tag', ['tag' => $player->clan()->tag()]))->toString();
      }
      if ($player->league()) {
        $league = [
          '#theme' => 'image',
          '#uri' => $player->league()->iconUrls()->small(),
          // '#width' => 288,
          // '#height' => 288,
        ];
      } else {
        $league = '';
      }

      $items = [
        // \Drupal::service('renderer')->render($badge),
        \Drupal::service('renderer')->render($league),
        'tag: ' . $player->tag(),
        ['#markup' => 'clan: ' . $clan],
        'role: ' . $player->role(),
        'attackWins: ' . $player->attackWins(),
        'defenseWins: ' . $player->defenseWins(),
        'townHallLevel: ' . $player->townHallLevel(),
        'townHallWeaponLevel: ' . $player->townHallWeaponLevel(),
        'versusBattleWins: ' . $player->versusBattleWins(),
        'expLevel: ' . $player->expLevel(),
        'trophies: ' . $player->trophies(),
        'bestTrophies: ' . $player->bestTrophies(),
        'builderHallLevel: ' . $player->builderHallLevel(),
        'versusTrophies: ' . $player->versusTrophies(),
        'bestVersusTrophies: ' . $player->bestVersusTrophies(),
        'versusBattleWinCount: ' . $player->versusBattleWinCount(),
        'warStars: ' . $player->warStars(),
      ];

    }
    catch (RequestException $error) {
      $logger = \Drupal::logger('ClashOfClans getPlayer error');
      $logger->error($error->getMessage());
    }

    $build['player'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $items,
    ];

    return $build;
  }

  public function setTitle($tag) {
    $client = $this->client;
    $clan = $client->getPlayer($tag);
    return $clan->name();
  }

}
