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
    if (isset($data['name']) && isset($data['tag'])) {
      $entity = $storage->create([
        'tag' => $data['tag'],
        'uid' => 1,
      ]);
      $fields = $this->getFields($data);
      $entity = $this->setEntity($entity, $fields);
      $entity->save();
      \Drupal::messenger()->addMessage('Clan data created.');
      return $entity;
    }
  }

  // get field data
  public function getFields($data) {
    $fields = [];
    if (isset($data['name'])) $fields['title'] = $data['name'];
    if (isset($data['description'])) $fields['description'] = $data['description'];
    if (isset($data['isWarLogPublic'])) $fields['field_public'] = $data['isWarLogPublic'];
    if (isset($data['type'])) $fields['field_type'] = $data['type'];
    if (isset($data['warLeague']['id'])) $fields['field_warleague'] = ['target_id' => $data['warLeague']['id']];
    return $fields;
  }

  public function setEntity($entity, $fields) {
    foreach ($fields as $key => $field) {
      $entity->set($key, $field);
    }
    return $entity;
  }

  public function getLiveData($entity) {
    $tag = $entity->tag->value;
    if ($tag) {
      $client = $this->client;
      $url = 'clans/'. $tag;
      $data = $client->getData($url);
      if ($data) {
        $this->updateEntity($entity, $data); //update entity if needed.
        $data['entity_id'] = $entity->id();
        return $data;
      }
    }
  }


  // public function prepareView($entity) {
  //   $tag = $entity->tag->value;
  //   if ($tag) {
  //     $client = $this->client;
  //     $url = 'clans/'. $tag;
  //     $json = $client->getJson($url);
  //     $data = \Drupal\Component\Serialization\Json::decode($json);
  //     $this->updateEntity($entity, $data);
  //     $entity->set('field_data', $json);  //keep entity view update.
  //
  //   }
  // }

  /**
  * Compare fields and update entity if outdated.
  **/
  public function updateEntity($entity, $data) {
    $diff = [];
    $fields = $this->getFields($data);
    foreach ($fields as $key => $field) {
      $value = $entity->get($key)->getString();
      if (\is_array($field)) {
        if ($value != current(array_values($field))) $diff[$key] = $field;
      } elseif ($value != $field) {
        $diff[$key] = $field;
      }
    }

    if ($diff) {
      $entity = $this->setEntity($entity, $diff);
      $entity->save();
      \Drupal::messenger()->addMessage('Clan data updated.');
    }

  }

  public function getWarlog($entity) {
    if (isset($entity->tag->value)) {
      $tag = $entity->tag->value;
      $url = 'clans/'. $tag. '/warlog';
      // $options = ['query' => ['limit' => 3]];
      // $data = $this->client->getData($url, $options);
      $data = $this->client->getData($url);
      $items = [];
      if (isset($data['items'])) {
        $items = $data['items'];
      }
      return $items;
    }
  }

}
