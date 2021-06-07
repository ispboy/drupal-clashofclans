<?php
/**
* Clan class: fetch data from multi-source,  to build the full clan object.
* in development NOW!
**/
namespace Drupal\clashofclans_api;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Clan implements ContainerInjectionInterface {
  private $client;

  public function __construct(\Drupal\clashofclans_api\Client $client) {
      $this->client = $client;
  }

  public static function create(ContainerInterface $container)
  {
      $client = $container->get('clashofclans_api.client');
      return new static($client);
  }

  public function get($tag, $limit = 0, $format = 'array') {
    $url = 'clans/' . urlencode($tag);
    $data = $this->client->getArray($url);
    $items = [];
    $count = 0;
    foreach ($data['memberList'] as $item) {
      $key = $item['tag'];
      $items[$key] = $item;
      if ($count < $limit) {
        $url = 'players/'. urlencode($key);
        $detail = $this->client->getArray($url);
        $items[$key]['attackWins'] = $detail['attackWins'];
        $items[$key]['defenseWins'] = $detail['defenseWins'];
        if (isset($detail['legendStatistics'])) {
          $items[$key]['legendStatistics'] = $detail['legendStatistics'];
        }
      }
    }

    $data['memberList'] = $items;

    return $this->client->encode($data, $format);
  }

}
