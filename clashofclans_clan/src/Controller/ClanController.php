<?php

namespace Drupal\clashofclans_clan\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_clan\Clan;

/**
 * Returns responses for ClashOfClans Clan routes.
 */
class ClanController extends ControllerBase {

  private $clan;

  public function __construct(Clan $clan) {
    $this->clan = $clan;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('clashofclans_clan.clan')
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
}
