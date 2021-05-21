<?php

namespace Drupal\clashofclans_clan\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Returns responses for ClashOfClans Clan routes.
 */
class ClanController extends ControllerBase {

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

  public function setTitle($tag) {
    $title = $tag;
    $clan = $this->client->get('getClan', ['tag' => $tag]);
    if (!empty($clan)) {
      $title = $clan->name();
    }
    return $title;
  }

  /**
   * Builds the response.
   */
  public function tag($tag) {
    $clan = $this->client->get('getClan', ['tag' => $tag]);

    if (empty($clan)) {
      $build['content'] = [
        '#type' => 'item',
        '#markup' => $this->t('Not found!'),
      ];

      return $build;
    }

    $badge = [
      '#theme' => 'image',
      '#uri' => $clan->badgeUrls()->small(),
    ];
    if ($clan->location()) {
      $location = Link::fromTextAndUrl($clan->location()->name(), Url::fromRoute('entity.clashofclans_location.canonical', ['clashofclans_location' => $clan->location()->id()]))->toString();
    } else {
      $location = '';
    }
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
}
