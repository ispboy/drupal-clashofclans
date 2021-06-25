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

  public function userTitle(\Drupal\user\UserInterface $user = NULL) {
    $result = '';
    if ($user) {
      $name = $user->get('field_player_name')->getString();
      if ($name) {
        $result = [
          '#markup' => $name,
          '#allowed_tags' => \Drupal\Component\Utility\Xss::getHtmlTagList(),
        ];
      } else {
        $result = [
          '#markup' => $user->getDisplayName(),
          '#allowed_tags' => \Drupal\Component\Utility\Xss::getHtmlTagList(),
        ];
      }
    }

    return $result;
  }

  /**
   * Builds the response.
   */
  public function tag($tag) {
    $route = 'entity.user.canonical';

    $id = $this->player->getUserId($tag);
    if ($id) {
      return $this->redirect($route, ['user' => $id]);
    }

    $build['content'] = [
      '#markup' => $this->t('Not found!'),
    ];

    return $build;

  }

  /**
   * Builds the response.
   */
  public function verifyToken($tag) {

    $build['content'] = ['#markup' => $this->t('No results.')];

    return $build;

  }

}
