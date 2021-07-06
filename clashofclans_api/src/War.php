<?php
/**
*  Process data from War
*  Connect to drupal entity
**/
namespace Drupal\clashofclans_api;

use Drupal\clashofclans_api\Client;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class War {
  private $client;
  private $entityTypeManager;
  private $data;

  public function __construct(Client $client, EntityTypeManagerInterface $entityTypeManager) {
    $this->client = $client;
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('clashofclans_api.client'),
      $container->get('entity_type.manager'),
    );
  }

  public function getEntity($tag, $field='clan_tag') {
    $urls = [
      'clan_tag' => 'clans/'. urlencode($tag). '/currentwar',
      'war_tag' => 'clanwarleagues/wars/'. urlencode($tag),
    ];
    $url = $urls[$field];
    $data = $this->client->get($url);
    if (isset($data['state'])) {
      $state = $data['state'];
      if ($state != 'notInWar') {
        $storage = $this->entityTypeManager->getStorage('clashofclans_war');
        $query = $storage->getQuery();
        $query -> condition($field, $tag);
        if ($field == 'clan_tag') {
          $preparationStartTime = $data['preparationStartTime'];
          
        }
        $ids = $query->execute();
      }
    }
  }

  public function clanWarLeagues($tag) { //get clan war Leagues' war data
    $url = 'clanwarleagues/wars/'. urlencode($tag);
    $data = $this->client->get($url);
    if (isset($data['state'])) {
      $this->data = $data;
    } else {
      $this->data = NULL;
    }
  }

  public function getData() {
    return $this->data;
  }


}
