<?php
/**
*  Process data from Clan
*  Connect to drupal entity
**/
namespace Drupal\clashofclans_api;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\clashofclans_api\Client;

class Clan {
  protected $client;
  protected $entityTypeManager;
  protected $entityClans;

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

  public function getName($tag) {
    $entity = $this->getEntity($tag);
    if ($entity) {
      return $entity->get('title')->getString();
    }
  }

  public function getDescription($tag) {
    $entity = $this->getEntity($tag);
    if ($entity) {
      return $entity->get('description')->getString();
    }
  }

  public function getEntityId($tag) {
    $entity = $this->getEntity($tag);
    if ($entity) {
      return $entity->id();
    }
  }

  public function getEntity($tag) {
    if (!isset($this->entityClans[$tag])) {
      $this->connect($tag);
    }
    if (isset($this->entityClans[$tag])) {
      $entity = $this->entityClans[$tag];
      return $entity;
    }
  }
  /**
  * Get or create/update
  **/
  protected function connect($tag) {
    $storage = $this->entityTypeManager->getStorage('clashofclans_clan');
    $query = $storage->getQuery();
    $query -> condition('clan_tag', $tag);
    $ids = $query->execute();
    if ($ids) { //entity exists.
      $id = current($ids);
      $this->entityClans[$tag] = $storage->load($id);
      return TRUE;
    } else {  // create new
      $url = 'clans/'. urlencode($tag);
      $data = $this->client->get($url);
      // dpm(array_keys($data));
      if (isset($data['tag'])) {
        $entity = $storage->create([
          'title' => $data['name'],
          'description' => $data['description'],
          'clan_tag' => $data['tag'],
          'uid' => 1,
        ]);
        $entity->save();
        $this->entityClans[$tag] = $entity;
        return TRUE;
      }
    }
  }

}
