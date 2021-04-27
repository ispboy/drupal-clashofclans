<?php

namespace Drupal\clashofclans_player\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans\ClashofclansClient;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Returns responses for ClashOfClans Player routes.
 */
class PlayerController extends ControllerBase {
  private $client;

  public function __construct(ClashofclansClient $client)
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
  public function tag($tag) {

    $player = $this->client->get('getPlayer', ['tag' => $tag]);

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
      'rank: '. (isset($player->legendStatistics()['currentSeason']['rank']) ? $player->legendStatistics()['currentSeason']['rank'] : ''),
      'bestTrophies: ' . $player->bestTrophies(),
      'builderHallLevel: ' . $player->builderHallLevel(),
      'versusTrophies: ' . $player->versusTrophies(),
      'bestVersusTrophies: ' . $player->bestVersusTrophies(),
      'versusBattleWinCount: ' . $player->versusBattleWinCount(),
      'warStars: ' . $player->warStars(),
    ];

    $build['player'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $items,
    ];

    return $build;
  }

  public function setTitle($tag) {
    $title = $tag;
    $player = $this->client->get('getPlayer', ['tag' => $tag]);
    if (!empty($player)) {
      $title = $player->name();
    }
    return $title;
  }

}
