<?php

namespace Drupal\clashofclans_leaguegroup\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_api\LeagueGroup;

/**
 * Returns responses for ClashOfClans Clan routes.
 */
class LeaguegroupController extends ControllerBase {
  private $leaguegroup;

  public function __construct(LeagueGroup $leaguegroup) {
      $this->leaguegroup = $leaguegroup;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('clashofclans_api.leaguegroup'),
    );
  }

  public function currentWar(EntityInterface $clashofclans_clan) {
    $tag = $clashofclans_clan->get('clan_tag')->getString();
    $data = $this->leaguegroup->fetchData($tag);
    if ($data) {
      if ($data['state'] == 'ended') {
      // if ($data['state'] == 'inWar') {
        $id = $this->leaguegroup->getEntityId($tag, $data);
        if (!$id) {
          $title = $clashofclans_clan->get('title')->getString();
          $id = $this->leaguegroup->createEntity($data, $title);
        }
        $route = 'entity.leaguegroup.canonical';
        return $this->redirect($route, ['leaguegroup' => $id]);

      } else {
        $data = $this->leaguegroup->processData($data);
        $build['content'] = [
          '#theme' => 'leaguegroup_data',
          '#data' => $data,
        ];
      }
    } else {
      $build['content'] = ['#markup' => $this->t('No results.')];
    }

    $build['#cache']['max-age'] = $this->leaguegroup->getCacheMaxAge();
    return $build;
  }

}
