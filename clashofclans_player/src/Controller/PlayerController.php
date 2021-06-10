<?php

namespace Drupal\clashofclans_player\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans\ClashofclansClient;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Returns responses for ClashOfClans Player routes.
 */
class PlayerController extends ControllerBase {
  private $client;

  public function __construct(\Drupal\clashofclans_api\Client $client)
  {
      $this->client = $client;
  }

  public static function create(ContainerInterface $container)
  {
      $client = $container->get('clashofclans_api.client');
      return new static($client);
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

  /**
   * Builds the response.
   */
  public function tag($tag) {
    $url = 'players/'. urlencode($tag);
    $data = $this->client->get($url);

    if (!isset($data['name'])) {
      $build['content'] = [
        '#markup' => $this->t('Not found!'),
      ];

      return $build;
    }

    $build['content'] = [
      '#theme' => 'clashofclans_player_tag',
      '#player' => $data,
    ];

    if (isset($data['clan'])) {
      $clan = \Drupal\clashofclans_api\Link::clan($data['clan']['name'], $data['clan']['tag']);
      $build['content']['#clan'] = $clan;
    }

    $build['content']['#cache']['max-age'] = $this->client->getCacheMaxAge();

    return $build;

  }
}
