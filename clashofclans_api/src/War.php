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
  private $entities;  //entities[$tag.$type]

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

  public function getEntity($tag, $type='clan_war') {
    if (!isset($this->entities[$tag.$type])) {
      $this->connect($tag, $type);
    }
    return $this->entities[$tag.$type];
  }

  public function connect($tag, $type='clan_war') {
    $urls = [
      'clan_war' => 'clans/'. urlencode($tag). '/currentwar',
      'league_war' => 'clanwarleagues/wars/'. urlencode($tag),
    ];
    $url = $urls[$type];
    $data = $this->client->get($url);
    if (isset($data['state'])) {
      $state = $data['state'];
      if ($state != 'notInWar') {
        $storage = $this->entityTypeManager->getStorage('clashofclans_war');
        $query = $storage->getQuery();
        $query -> condition('bundle', $type);
        $query -> condition('tag', $tag);
        $start_time = $this->client->strToDatetime($data['startTime']);
        dpm($data['startTime']);
        dpm($start_time);
        if ($type == 'clan_war') {
          $query -> condition('start_time', $start_time);
        }
        $ids = $query->execute();
        if ($ids) {
          $entity = $storage->load(current($ids));
          // $entity->set('start_time', $start_time);
          // $entity->save();
          $this->entities[$tag.$type] = $entity;
        } else {
          $title = implode(' ', [
            $data['clan']['name'], 'vs', $data['opponent']['name']
          ]);
          $entity = $storage->create([
            'title' => $title,
            'bundle' => $type,
            'state' => $data['state'],
            'data' => \Drupal\Component\Serialization\Json::encode($data),
            'start_time' => [$start_time],
            'tag' => $tag,
            'uid' => 1,
          ]);
          $entity->save();
          $this->entities[$tag.$type] = $entity;
        }
      }
    }
  }

}
