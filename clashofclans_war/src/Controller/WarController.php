<?php

namespace Drupal\clashofclans_war\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_api\Client;

/**
 * Returns responses for ClashOfClans Clan routes.
 */
class WarController extends ControllerBase {
  private $client;

  public function __construct(Client $client) {
      $this->client = $client;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('clashofclans_api.client'),
    );
  }
  public function clanWarLeagues($tag) {
    $url = 'clanwarleagues/wars/'. urlencode($tag);
    $data = $this->client->get($url);
    if ($data) {
      if ($data['state'] == 'notInWar') {
        $build['content'] = [
          '#markup' => $this->t('Not in war.'),
        ];
      } else {
        $war = new \Drupal\clashofclans_api\CurrentWar($data);
        // dpm($war->getPlayers());
        $build['content'] = [
         '#theme' => 'clashofclans_clan_currentwar',
         '#war' => $war->getData(),
         '#players' => $war->getPlayers(),
        ];
      }
    } else {
      $build['content'] = ['#markup' => $this->t('No results.')];
    }

    $build['#cache']['max-age'] = $this->client->getCacheMaxAge();
    return $build;
  }

}
