<?php
namespace Drupal\clashofclans_player;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\clashofclans_api\GuzzleCache;

class Player {
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
    $storage = $this->entityTypeManager->getStorage('user');
    $query = $storage->getQuery();
    $query -> condition('field_tag', $tag);
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
    $storage = $this->entityTypeManager->getStorage('user');
    $url = 'players/'. $tag;
    $json = $this->client->getJson($url);
    $data = \Drupal\Component\Serialization\Json::decode($json);
    // dpm(array_keys($data));
    if (isset($data['name'])) {
      $user = $storage->create();
      $username = ltrim($tag, '#');
      $mail = $username. '@null.com';
      // Mandatory.
      $user->setPassword(user_password());
      $user->enforceIsNew();
      $user->setEmail($mail);
      $user->setUsername($username);
      // Optional.
      $user->set('init', $mail);
      $user->set('field_tag', $tag);

      $fields = $this->getFields($data);
      $user = $this->setEntity($user, $fields);

      $user->activate();
      $user->addRole('player');

      // Save user account.
      $user->save();
      \Drupal::messenger()->addMessage('Player data created.');
      return $user;
    }
  }

  public function getLiveData($entity) {
    $tag = $entity->field_tag->value;
    if ($tag) {
      $client = $this->client;
      $url = 'players/'. $tag;
      $data = $client->getData($url);
      if ($data) {
        $this->updateEntity($entity, $data); //update entity if needed.
        $data['entity_id'] = $entity->id();
        return $data;
      }
    }
  }

  /**
  * Compare fields and update entity if outdated.
  **/
  public function updateEntity($entity, $data) {
    $diff = [];
    $fields = $this->getFields($data);
    foreach ($fields as $key => $field) {
      if (\is_array($field)) {
        continue; //omit array().. especially Season
      }

      $values = $entity->get($key)->getValue();
      $value = end($values);
      if (isset($value['value']) && $value['value'] == $field) {
        continue;
      }

      $diff[$key] = $field;
    }

    if (array_key_exists('field_legend_trophies', $diff)) {
      if (isset($fields['field_best_season'])) $diff['field_best_season'] = $fields['field_best_season'];
      if (isset($fields['field_previous_season'])) $diff['field_previous_season'] = $fields['field_previous_season'];
    }

    if ($diff) {
      $entity = $this->setEntity($entity, $diff);
      $entity->save();
      \Drupal::messenger()->addMessage('Player data updated.');
    }

  }

  // get field data
  public function getFields($data) {
    $fields = [];
    if (isset($data['name'])) $fields['field_name'] = $data['name'];
    if (isset($data['warStars'])) $fields['field_war_stars'] = $data['warStars'];
    if (isset($data['legendStatistics']['legendTrophies'])) $fields['field_legend_trophies'] = $data['legendStatistics']['legendTrophies'];
    if (isset($data['legendStatistics']['bestSeason'])) {
      $fields['field_best_season'] = $this->convertSeason($data['legendStatistics']['bestSeason']);
    }
    if (isset($data['legendStatistics']['previousSeason'])) {
      $fields['field_previous_season'] = $this->convertSeason($data['legendStatistics']['previousSeason']);
    }
    return $fields;
  }

  public function setEntity($entity, $fields) {
    foreach ($fields as $key => $field) {
      if ($key == 'field_name') {
        $entity->field_name->appendItem($field);
      } else {
        $entity->set($key, $field);
      }
    }
    return $entity;
  }

  /**
  * Get or create/update User.
  * Return uid.
  **/
  public function verifyToken($tag, $token, &$status) {
    $url = 'players/'. $tag. '/verifytoken';
    $body = json_encode(['token' => $token]);
    $options = ['body' => $body];
    $data = $this->client->postData($url, $options);
    if (isset($data['status'])) {
      $status = $data['status'];
      if ($status == 'ok') {
        $uid = $this->getEntityId($tag);
        $storage = $this->entityTypeManager->getStorage('user');
        $user = $storage->load($uid);
        user_login_finalize($user);
        return $uid;
      }
    }
  }


  public function convertSeason($season) {
    if (isset($season['id'])) {
      $timestamp = strtotime($season['id']);
      $date = date('Y-m-d', $timestamp);
      $season['id'] = $date; //convert 2021-06 to 2021-06-01
    }
    return $season;
  }
}
