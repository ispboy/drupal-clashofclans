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
    $title = $this->war->getTitle($tag, 'league_war');
    if ($title) {
      return $title;
    } else {
      return $tag;
    }
  }

  public function clanWarLeagues($tag) {
    $entity = $this->war->getEntity($tag, 'league_war');
    if ($entity) {
      $route = 'entity.clashofclans_war.canonical';
      return $this->redirect($route, ['clashofclans_war' => $entity->id()]);
    }
    
    $data = $this->war->getData($tag, 'league_war');
    if ($data) {
      $build['content'] = [
        '#theme' => 'clashofclans_war_data',
        '#data' => $data,
      ];
    } else {
      $build['content'] = ['#markup' => $this->t('No results.')];
    }

    $build['#cache']['max-age'] = $this->war->getCacheMaxAge();
    return $build;
  }

}
