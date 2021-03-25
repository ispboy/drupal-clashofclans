<?php

namespace Drupal\clashofclans_clan\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\clashofclans\ClashofclansCore;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Returns responses for ClashOfClans Clan routes.
 */
class RankingController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function global() {

    $rankings = ClashofclansCore::getRankingsForLocation('global', 'clans');
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

}
