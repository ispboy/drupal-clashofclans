<?php

namespace Drupal\clashofclans_player\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_player\Player;

/**
 * Returns responses for ClashOfClans Player routes.
 */
class PlayerController extends ControllerBase {
  private $player;

  public function __construct(Player $player) {
      $this->player = $player;
  }

  public static function create(ContainerInterface $container) {
      return new static(
        $container->get('clashofclans_player.player'),
      );
  }

  public function setTitle($tag) {
    $title = $tag;  //provide default title, if not found.
    $url = 'players/'. $tag;
    $data = $this->player->client->getData($url);

    if (isset($data['name'])) {
      $title = $data['name'];
    }
    return $title;
  }

  /**
   * Builds the response.
   */
  public function tag($tag) {
    $route = 'entity.user.canonical';

    $id = $this->player->getEntityId($tag);
    if ($id) {
      return $this->redirect($route, ['user' => $id], [], 301);
    } else {
      $build['content'] = ['#markup' => $this->t('No results.')];
      return $build;
    }

  }

}
