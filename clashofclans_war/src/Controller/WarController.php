<?php

namespace Drupal\clashofclans_war\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_war\War;

/**
 * Returns responses for ClashOfClans Clan routes.
 */
class WarController extends ControllerBase {

  private $war;

  public function __construct(War $war) {
    $this->war = $war;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('clashofclans_war.war'),
    );
  }

  public function setTitle($tag) {
    $url = 'clanwarleagues/wars/'. $tag;
    $data = $this->war->getClient()->getData($url);
    return $this->war->getTitle($data);
  }

  /**
   * Builds the response.
   */
  public function tag($tag) {
    $route = 'entity.clashofclans_war.canonical';

    $result = $this->war->tag($tag);
    // $build['content'] = ['#markup' => $this->t('No results.')];
    // return $build;
    if (isset($result['id'])) {
      return $this->redirect($route, ['clashofclans_war' => $result['id']]);
    }

    if (isset($result['data'])) {
      $build['content'] = [
        '#theme' => 'clashofclans_war_data',
        '#data' => $result['data'],
      ];
    } else {
      $build['content'] = ['#markup' => $this->t('No results.')];
    }

    $build['#cache']['max-age'] = $this->war->getClient()->getMaxAge();
    return $build;

  }
}
