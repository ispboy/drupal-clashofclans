<?php
namespace Drupal\clashofclans_api;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\clashofclans_api\Client;

class Player {
  protected $client;
  protected $entityTypeManager;

  public function __construct(
    Client $client,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
      $this->client = $client;
      $this->entityTypeManager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container) {
      return new static(
        $container->get('clashofclans_api.client'),
        $container->get('entity_type.manager'),
      );
  }

  public function getEntityTypeManager() {
    return $this->entityTypeManager;
  }

  /**
  * Get or create/update User
  **/
  public function getEntityId($tag) {
    $id = 0;
    $storage = $this->entityTypeManager->getStorage('user');
    $query = $storage->getQuery();
    $query -> condition('field_player_tag', $tag);
    $ids = $query->execute();
    if ($ids) { //entity exists.
      $id = current($ids);
    } else {  // create new
      $url = 'players/'. urlencode($tag);
      $data = $this->client->get($url);
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
        $user->set('field_player_tag', $tag);
        $user->set('field_player_name', $data['name']);
        $user->activate();
        $user->addRole('player');
        $this->setLegendStatistics($data, $user);

        // Save user account.
        $user->save();
        $id = $user->id();
      }
    }
    return $id;
  }

  /**
  * set legendStatistics data if outdated.
  * return TRUE if set, or False.
  */
  public function setLegendStatistics($data, &$entity) {
    $fields = [
      'legendTrophies' => 'field_legend_trophies',
      'bestSeason' => 'field_best_season',
      'previousSeason' => 'field_previous_season',
      'bestVersusSeason' => 'field_best_versus_season',
      'previousVersusSeason' => 'field_previous_versus_season',
    ];

    $count = 0;
    foreach ($fields as $key=>$field) {
      if (isset($data['legendStatistics'][$key])) {
        $value = $data['legendStatistics'][$key];
        $type = $entity->get($field)->getFieldDefinition()->getType();
        switch ($type) {
          case 'clashofclans_player_season':
            if (isset($value['id'])) {
              $timestamp = strtotime($value['id']);
              $date = date('Y-m-d', $timestamp);
              $value['id'] = $date; //convert 2021-06 to 2021-06-01
              $season = $entity->get($field)->getValue();
              if (isset($season[0]['id'])) {
                $date = $season[0]['id'];
                if (strcmp($value['id'], $date)) {
                  $entity->set($field, $value);
                  $count ++;
                }
              } else {
                $entity->set($field, $value);
                $count ++;
              }
            }
            break;
          default:
            $string = $entity->get($field)->getString();
            if (strcmp($string, $value)) {
              $entity->set($field, $value);
              $count ++;
            }
        }
      }
    }

    if (isset($data['bestTrophies'])) {
      $bestTrophies = $data['bestTrophies'];
      $field = 'field_best_trophies';
      $old = $entity->get($field)->getString();
      if (strcmp($old, $bestTrophies)) {
        $entity->set($field, $bestTrophies);
        $count ++;
      }
    }

    if ($count) {
      return TRUE;
    }

  }

  /**
  * Get or create/update User.
  * Return uid.
  **/
  public function verifyToken($tag, $token, &$status) {
    $url = 'players/'. urlencode($tag). '/verifytoken';
    $body = json_encode(['token' => $token]);
    $data = $this->client->post($url, $body);
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

  /**
  * Get pets from troops.
  * Return array.
  **/
  public function getPets($data) {

  }

}
