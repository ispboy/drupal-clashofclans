<?php
namespace Drupal\clashofclans_clan;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\clashofclans_api\GuzzleCache;

class Clan {
  public $client;
  protected $entityTypeManager;

  public function __construct(
    GuzzleCache $client,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
      $this->client = $client;
      $this->entityTypeManager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container) {
      return new static(
        $container->get('clashofclans_api.guzzle_cache'),
        $container->get('entity_type.manager'),
      );
  }

  /**
  * Get uid
  **/
  public function getEntityId($tag) {
    $storage = $this->entityTypeManager->getStorage('clashofclans_clan');
    $query = $storage->getQuery();
    $query -> condition('tag', $tag);
    $ids = $query->execute();
    if ($ids) { //entity exists.
      $id = current($ids);
    } else {  // create new
      $entity = $this->createEntity($tag);
      if ($entity) {
        $id = $entity->id();
      }
    }
    return $id;
  }

  public function createEntity($tag) {
    $storage = $this->entityTypeManager->getStorage('clashofclans_clan');
    $url = 'clans/'. $tag;
    $json = $this->client->getJson($url);
    $data = \Drupal\Component\Serialization\Json::decode($json);
    // dpm(array_keys($data));
    if (isset($data['name'])) {
      $entity = $storage->create([
        'title' => $data['name'],
        'description' => $data['description'],
        'tag' => $data['tag'],
        'uid' => 1,
        'field_data' => $json,
      ]);
      $entity->save();
      return $entity;
    }
  }

  public function prepareView($entity) {
    $tag = $entity->tag->value;
    if ($tag) {
      $client = $this->client;
      $url = 'clans/'. $tag;
      $json = $client->getJson($url);
      $data = \Drupal\Component\Serialization\Json::decode($json);
      $outdated = $this->entityOutdated($entity, $data);
      $entity->set('field_data', $json);  //keep entity view update.
      if ($outdated) {
        $entity->set('description', $data['description']);
        $entity->save();
        \Drupal::messenger()->addMessage('This clan data updated.');
      }
    }
  }

  public function entityOutdated($entity, $data) {
    if (!$entity->field_data->value) {
      return TRUE;
    } else {
      $field = \Drupal\Component\Serialization\Json::decode($entity->field_data->value);
      $keys = ['description', 'isWarLogPublic'];
      foreach ($keys as $key) {
        if (strcmp($data[$key], $field[$key]) !== 0) {
          return TRUE;
        }
      }
    }
  }

}
