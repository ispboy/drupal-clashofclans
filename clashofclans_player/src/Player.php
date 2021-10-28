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
      $user->set('field_name', $data['name']);
      $user->set('field_data', $json);
      $user->activate();
      $user->addRole('player');

      // Save user account.
      $user->save();
      return $user;
    }
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

  public function prepareView($entity) {
    $tag = $entity->field_tag->value;
    if ($tag) {
      $client = $this->client;
      $url = 'players/'. $tag;
      $json = $client->getJson($url);
      $data = \Drupal\Component\Serialization\Json::decode($json);
      $outdated = $this->entityOutdated($entity, $data);
      $entity->set('field_data', $json);  //keep entity view update.
      if ($outdated) {
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
      $keys = [
        'name', 'townHallLevel', 'townHallWeaponLevel', 'warStars', 'role', 'warPreference'
      ];
      foreach ($keys as $key) {
        if (isset($data['townHallWeaponLevel']) && strcmp($data[$key], $field[$key]) !== 0) {
          return TRUE;
        }
      }

      if (isset($data['legendStatistics']['legendTrophies']) && isset($field['legendStatistics']['legendTrophies'])) {
        if (strcmp($data['legendStatistics']['legendTrophies'], $field['legendStatistics']['legendTrophies']) !== 0) {
          return TRUE;
        }
      }

      if (isset($data['clan']['tag']) && isset($field['clan']['tag'])) {
        if (strcmp($data['clan']['tag'], $field['clan']['tag']) !== 0) {
          return TRUE;
        }
      }

    }
  }

}
