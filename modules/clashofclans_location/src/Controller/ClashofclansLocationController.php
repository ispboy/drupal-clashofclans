<?php

namespace Drupal\clashofclans_location\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Returns responses for Clashofclans location routes.
 */
class ClashofclansLocationController extends ControllerBase {
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

  /**
   * Builds the response.
   */
  public function clans(EntityInterface $clashofclans_location) {

    $rows = [];
    $rankings = $this->client->get('getRankingsForLocation', ['id' => $clashofclans_location->id(), 'type' => 'clans']);
    foreach($rankings as $key => $ranking) {
      $badge = [
        '#theme' => 'image',
        '#uri' => $ranking->badgeUrls()->small(),
        '#width' => 64,
        '#height' => 64,
      ];
      $tag = $ranking->tag();
      $name = Link::fromTextAndUrl($ranking->name(), Url::fromRoute('clashofclans_clan.tag', ['tag' => $tag]))->toString();
      $rows[] = [
        $ranking->rank(),
        $ranking->previousRank(),
        \Drupal::service('renderer')->render($badge),
        $name,
        $ranking->clanLevel(),
        $ranking->members(),
        $ranking->clanPoints()
      ];
    }

    $header = ['rank', 'previousRank', 'badge', 'name', 'clanLevel', 'members','clanPoints'];
    $build['content'] = [
      '#type' => 'table',
      '#sticky' => TRUE,
      '#header' => $header,
      '#rows' => $rows,
    ];

    return $build;
  }

  /**
   * Builds the Players response.
   */
  public function players(EntityInterface $clashofclans_location) {

    $rows = [];

    $rankings = $this->client->get('getRankingsForLocation', ['id' => $clashofclans_location->id(), 'type' => 'players']);
      // $first = current($rankings);
      // ksm($first->clan()->name());
    foreach($rankings as $key => $ranking) {
      $tag = $ranking->tag();
      $name = Link::fromTextAndUrl($ranking->name(), Url::fromRoute('clashofclans_player.tag', ['tag' => $tag]))->toString();

      $clan = '';
      if ($ranking->clan()) {
        $clan = Link::fromTextAndUrl($ranking->clan()->name(), Url::fromRoute('clashofclans_clan.tag', ['tag' => $ranking->clan()->tag()]))->toString();
      }

      $league = [
        '#theme' => 'image',
        '#uri' => $ranking->league()->iconUrls()->small(),
        '#width' => 36,
        '#height' => 36,
      ];

      $rows[] = [
        $ranking->rank(),
        $ranking->previousRank(),
        $name,
        \Drupal::service('renderer')->render($league),
        $ranking->expLevel(),
        $ranking->trophies(),
        $ranking->attackWins(),
        $ranking->defenseWins(),
        $clan,
      ];
    }


    $header = ['rank', 'previousRank', 'name', 'league', 'expLevel', 'trophies','attackWins', 'defenseWins', 'clan'];
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
