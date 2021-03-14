<?php

namespace Drupal\clashofclans_clan\Controller;

use ClashOfClans\Client;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;

/**
 * Returns responses for ClashOfClans Clan routes.
 */
class ClashofclansClanController extends ControllerBase {

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
      $rankings = $client->getRankingsForLocation('global', 'clans'); // returns array of Clan objects
      foreach($rankings as $key => $ranking) {
        $location = Link::fromTextAndUrl($ranking->location()->name(), Url::fromRoute('clashofclans_location.clans', ['clashofclans_location' => $ranking->location()->id()]))->toString();
        $tag = $ranking->tag();
        $name = Link::fromTextAndUrl($ranking->name(), Url::fromRoute('clashofclans_clan.tag', ['tag' => $tag]))->toString();
        $rows[] = [
          $ranking->rank(),
          $ranking->previousRank(),
          // \Drupal::service('renderer')->render($badge),
          $name,
          $location,
          $ranking->clanLevel(),
          $ranking->members(),
          $ranking->clanPoints()];
      }
    }
    catch (RequestException $error) {
      $logger = \Drupal::logger('ClashOfClans Client error');
      $logger->error($error->getMessage());
    }

    $header = ['rank', 'previousRank', 'name', 'location', 'clanLevel', 'members','clanPoints'];
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

    $clan = $client->getClan($tag);
    $badge = [
      '#theme' => 'image',
      '#uri' => $clan->badgeUrls()->small(),
    ];
    $location = Link::fromTextAndUrl($clan->location()->name(), Url::fromRoute('clashofclans_location.clans', ['clashofclans_location' => $clan->location()->id()]))->toString();
    $items = [
      \Drupal::service('renderer')->render($badge),
      'tag: ' . $clan->tag(),
      'type: ' . $clan->type(),
      'description: ' . $clan->description(),
      ['#markup' => 'location: ' . $location],
      'clanLevel: '. $clan->clanLevel(),
      'clanPoints: '. $clan->clanPoints(),
      'clanVersusPoints: '. $clan->clanVersusPoints(),
      'requiredTrophies: '. $clan->requiredTrophies(),
      'warFrequency: '. $clan->warFrequency(),
      'warTies: '. $clan->warTies(),
      'warLosses: '. $clan->warLosses(),
      'warWins: '. $clan->warWins(),
      'warWinStreak: '. $clan->warWinStreak(),
      'isWarLogPublic: '. $clan->isWarLogPublic(),
    ];
    $build['clan'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $items,
    ];

    $all = $clan->memberList()->all();
    $rows = [];
    foreach($all as $key => $player) {
      $rows[] = [
        $player->clanRank(),
        $player->previousClanRank(),
        $player->tag(),
        $player->name(),
        $player->role(),
        $player->expLevel(),
        $player->trophies(),
        $player->versusTrophies(),
        $player->donations(),
        $player->donationsReceived(),
      ];
    }

    $header = ['Rank', 'previous', 'tag', 'name', 'role', 'expLevel', 'trophies', 'versusTrophies', 'donations', 'Received'];
    $build['member'] = [
      '#caption' => 'members: '. $clan->members(),
      '#type' => 'table',
      '#sticky' => TRUE,
      '#header' => $header,
      '#rows' => $rows,
    ];

    return $build;
  }

  public function setTitle($tag) {
    $client = $this->client;
    $clan = $client->getClan($tag);
    return $clan->name();
  }
}
