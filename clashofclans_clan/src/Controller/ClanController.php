<?php

namespace Drupal\clashofclans_clan\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_clan\Clan;
use Drupal\clashofclans_leaguegroup\LeagueGroup;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

/**
 * Returns responses for ClashOfClans Clan routes.
 */
class ClanController extends ControllerBase {

  private $clan;
  private $league_group;

  public function __construct(Clan $clan, LeagueGroup $league_group) {
    $this->clan = $clan;
    $this->league_group = $league_group;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('clashofclans_clan.clan'),
      $container->get('clashofclans_leaguegroup.league_group')
    );
  }

  public function setTitle($tag) {
    $title = $tag;  //provide default title, if not found.
    $url = 'clans/'. $tag;
    $data = $this->clan->client->getData($url);

    if (isset($data['name'])) {
      $title = $data['name'];
    }
    return $title;
  }

  /**
   * Builds the response.
   */
  public function tag($tag) {
    $route = 'entity.clashofclans_clan.canonical';

    $id = $this->clan->getEntityId($tag);
    if ($id) {
      return $this->redirect($route, ['clashofclans_clan' => $id]);
    } else {
      $build['content'] = ['#markup' => $this->t('No results.')];
      return $build;
    }

  }

  /**
   * Builds the response.
   */
  public function getTitle($clashofclans_clan) {
    return $clashofclans_clan->label();
  }

  public function warlogAccess(AccountInterface $account, $clashofclans_clan) {
    // Using the storage controller (recommended).
    $entity = \Drupal::entityTypeManager()->getStorage('clashofclans_clan')->load($clashofclans_clan);
    $public = $entity->field_public->value;
    return AccessResult::allowedIf($public);
  }

  /**
   * Builds the response.
   */
  public function warlog($clashofclans_clan) {
    $items = $this->clan->getWarlog($clashofclans_clan);
    $header = [
      ['data' => '#'],
      ['data' => 'Result'],
      ['data' => 'endTime', 'class' => [RESPONSIVE_PRIORITY_LOW ]],
      ['data' => 'teamSize', 'class' => [RESPONSIVE_PRIORITY_LOW ]],
      ['data' => 'attacksPerMember', 'class' => [RESPONSIVE_PRIORITY_LOW ]],
      ['data' => 'clan'],
      ['data' => 'opponent'],

    ];
    $rows = [];
    $i = 0;
    foreach ($items as $item) {
      if (!$item['result']) {
        continue;
      }
      $row = [];
      $i++;
      $row[] = $i;
      $row[] = ['data' => $item['result']];
      $row[] = ['data' => \Drupal::service('date.formatter')->format(\strtotime(\str_replace('.000Z', ' UTC', $item['endTime'])))];
      $row[] = ['data' => $item['teamSize']];
      $row[] = ['data' => isset($item['attacksPerMember']) ? $item['attacksPerMember'] : ''];
      foreach (['clan', 'opponent'] as $key) {
        $renderable = [
          '#theme' => 'clashofclans_clan_warlog',
          '#data' => $item[$key],
        ];
        $row[] = \Drupal::service('renderer')->renderPlain($renderable);
      }
      $rows[] = ['data' => $row];
    }
    $renderable = [
      '#type' => 'table',
      // '#responsive' => FALSE,
      '#sticky' => TRUE,
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No results.'),
    ];

    $build['content'] = $renderable;
    $build['#cache']['max-age'] = $this->clan->client->getMaxAge();
    return $build;
  }

  /**
   * Builds the response.
   */
  public function leagueGroup($clashofclans_clan) {
    $data = $this->league_group->currentWar($clashofclans_clan);
    if ($data) {
      $build['content'] = [
        '#theme' => 'clashofclans_leaguegroup_currentwar',
        '#data' => $data,
      ];
    } else {
      $build['content'] = ['#markup' => $this->t('No results.')];
    }
    $build['content']['#cache']['max-age'] = $this->clan->client->getMaxAge();
    return $build;
  }

  /**
   * Builds the response.
   */
  public function members($clashofclans_clan) {
    $options = ['query' => ['limit' => 100]];
    $items = $this->clan->getMembers($clashofclans_clan, $options);

      $header = [
        'clanRank' => ['data' => '#'],
        'league' => ['data' => 'League', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        'expLevel' => ['data' => 'Exp', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        'name' => ['data' => 'Name'],
        // 'tag' => ['data' => 'Tag', 'class' => [RESPONSIVE_PRIORITY_LOW]],
        // 'role' => ['data' => 'Role', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        // 'donations' => ['data' => 'Donated', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        // 'donationsReceived' => ['data' => 'Received', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        // 'versusTrophies' => ['data' => 'Versus', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        'townHallLevel' => ['data' => 'TH', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        'attackWins' => ['data' => 'AW'],
        'defenseWins' => ['data' => 'DW'],
        'legendTrophies' => ['data' => 'legendTrophies', 'class' => [RESPONSIVE_PRIORITY_LOW]],
        'bestSeason' => ['data' => 'bestSeason', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        'bestRank' => ['data' => 'bestRank', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        'previousRank' => ['data' => 'previous', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
        'trophies' => ['data' => 'Trophies'],
      ];

    $build['content'] = \Drupal\clashofclans_api\Render::table($header, $items);
    $build['content']['#cache']['max-age'] = $this->clan->client->getMaxAge() * 10;

    return $build;
  }

}
