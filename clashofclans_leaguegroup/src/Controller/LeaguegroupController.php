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
    $entity = $this->leaguegroup->getEntity($tag);

    if ($entity) {
      $view_builder = $this->entityTypeManager()->getViewBuilder('leaguegroup');
      return $view_builder->view($entity);
    } else {
      return $build['content'] = ['#markup' => $this->t('No results.')];
    }
  }

}
