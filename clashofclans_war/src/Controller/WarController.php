<?php

namespace Drupal\clashofclans_war\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_api\War;

/**
 * Returns responses for ClashOfClans Clan routes.
 */
class WarController extends ControllerBase {
  protected $war;

  public function __construct(War $war) {
      $this->war = $war;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('clashofclans_api.war'),
    );
  }

  public function clanWarLeaguesTitle($tag) {
    $entity = $this->war->getEntity($tag, 'league_war');
    if ($entity) {
      return $entity->get('title')->getString();
    } else {
      return $tag;
    }
  }

  public function clanWarLeagues($tag) {
    $entity = $this->war->getEntity($tag, 'league_war');
    if ($entity) {
      $view_builder = $this->entityTypeManager()->getViewBuilder('clashofclans_war');
      return $view_builder->view($entity);
    } else {
      return $build['content'] = ['#markup' => $this->t('No results.')];
    }
  }

}
