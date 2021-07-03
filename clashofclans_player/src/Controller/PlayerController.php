<?php

namespace Drupal\clashofclans_player\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_api\Client;
use Drupal\clashofclans_api\Player;

/**
 * Returns responses for ClashOfClans Player routes.
 */
class PlayerController extends ControllerBase {
  private $client;
  private $player;

  public function __construct(Client $client, Player $player) {
      $this->client = $client;
      $this->player = $player;
  }

  public static function create(ContainerInterface $container) {
      return new static(
        $container->get('clashofclans_api.client'),
        $container->get('clashofclans_api.player')
      );
  }

  public function setTitle($tag) {
    $title = $tag;  //provide default title, if not found.
    $url = 'players/'. urlencode($tag);
    $data = $this->client->get($url);

    if (isset($data['name'])) {
      $title = $data['name'];
    }
    return $title;
  }

  // public function userTitle(\Drupal\user\UserInterface $user = NULL) {
  //   $result = '';
  //   if ($user) {
  //     $name = $user->get('field_player_name')->getString();
  //     if ($name) {
  //       $result = [
  //         '#markup' => $name,
  //         '#allowed_tags' => \Drupal\Component\Utility\Xss::getHtmlTagList(),
  //       ];
  //     } else {
  //       $result = [
  //         '#markup' => $user->getDisplayName(),
  //         '#allowed_tags' => \Drupal\Component\Utility\Xss::getHtmlTagList(),
  //       ];
  //     }
  //   }
  //
  //   return $result;
  // }

  /**
   * Builds the response.
   */
  public function tag($tag) {
    $route = 'entity.clashofclans_player.canonical';

    $id = $this->player->getEntityId($tag);
    if ($id) {
      return $this->redirect($route, ['clashofclans_player' => $id]);
    } else {
      $build['content'] = ['#markup' => $this->t('No results.')];
      return $build;
    }

  }

}
