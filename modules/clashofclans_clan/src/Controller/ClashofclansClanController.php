<?php

namespace Drupal\clashofclans_clan\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Exception\RequestException;
use ClashOfClans\Client;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Returns responses for ClashOfClans Clan routes.
 */
class ClashofclansClanController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function global() {
    $key = \Drupal::config('clashofclans.settings')->get('key');
    $client = new Client($key);

    try {
      $rankings = $client->getRankingsForLocation('global', 'clans'); // returns array of Clan objects
      foreach($rankings as $key => $ranking) {
        $location = Link::fromTextAndUrl($ranking->location()->name(), Url::fromRoute('clashofclans_location.clans', ['clashofclans_location' => $ranking->location()->id()]))->toString();
        $tag = $ranking->tag();
        $name = Link::fromTextAndUrl($ranking->name(), Url::fromRoute('clashofclans_clan.tag', ['tag' => $tag]))->toString();
        $badge = [
          '#theme' => 'image',
          '#uri' => $ranking->badgeUrls()->small(),
          '#width' => 64,
          '#height' => 64,
        ];
        $rows[] = [
          $ranking->rank(),
          $ranking->previousRank(),
          \Drupal::service('renderer')->render($badge),
          $name,
          $location,
          $ranking->clanLevel(),
          $ranking->members(),
          $ranking->clanPoints(),
        ];
      }
    }
    catch (RequestException $error) {
      $logger = \Drupal::logger('ClashOfClans Client error');
      $logger->error($error->getMessage());
    }

    $header = ['rank', 'previousRank', 'badge', 'name', 'location', 'clanLevel', 'members','clanPoints'];
    $build['content'] = [
      '#type' => 'table',
      '#sticky' => TRUE,
      '#header' => $header,
      '#rows' => $rows,
      '#cache' => [
        'keys' => ['clashofclans', 'global'],
        'max-age' => 60,
      ],
    ];

    return $build;
  }
  /**
   * Builds the response.
   */
  public function tag($tag) {
    $key = \Drupal::config('clashofclans.settings')->get('key');
    $client = new Client($key);

    try {
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
    }
    catch (RequestException $error) {
      if ($error->getCode() == 404) {
        $build['content'] = [
          '#type' => 'item',
          '#markup' => $this->t('Not Found.'),
        ];
        return $build;
      } else {
        $logger = \Drupal::logger('ClashOfClans getClan error');
        $logger->error($error->getMessage());
      }
    }

    $build['clan'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $items,
      '#cache' => [
        'keys' => ['clashofclans', 'tag', $tag, 'clan'],
        'max-age' => 60,
      ],
    ];

    $all = $clan->memberList()->all();
    $rows = [];
    foreach($all as $key => $player) {
      $name = Link::fromTextAndUrl($player->name(), Url::fromRoute('clashofclans_player.tag', ['tag' => $player->tag()]))->toString();
      $league = [
        '#theme' => 'image',
        '#uri' => $player->league()->iconUrls()->tiny(),
        '#width' => 36,
        '#height' => 36,
      ];
      $rows[] = [
        $player->clanRank(),
        $player->previousClanRank(),
        $player->tag(),
        $name,
        $player->role(),
        $player->expLevel(),
        $player->donations(),
        $player->donationsReceived(),
        $player->versusTrophies(),
        $player->trophies(),
        \Drupal::service('renderer')->render($league),
      ];
    }

    $header = ['Rank', 'previous', 'tag', 'name', 'role', 'expLevel', 'donations', 'Received', 'versusTrophies', 'trophies', 'league'];
    $build['member'] = [
      '#caption' => 'members: '. $clan->members(),
      '#type' => 'table',
      '#sticky' => TRUE,
      '#header' => $header,
      '#rows' => $rows,
      '#cache' => [
        'keys' => ['clashofclans', 'tag', $tag, 'member'],
        'max-age' => 60,
      ],
    ];

    return $build;
  }

  public function setTitle($tag) {
    $key = \Drupal::config('clashofclans.settings')->get('key');
    $title = $tag;
    try {
      $client = new Client($key);
      $clan = $client->getClan($tag);
      $title = $clan->name();
    }
    catch (RequestException $error) {

    }
    return $title;
  }
}
