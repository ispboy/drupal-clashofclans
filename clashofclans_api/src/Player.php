<?php
namespace Drupal\clashofclans_api;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\clashofclans_api\Client;

class Player {
  protected $client;
  protected $languageManager;
  protected $entityTypeManager;

  public function __construct(
    Client $client,
    EntityTypeManagerInterface $entityTypeManager,
    LanguageManager $languageManager
  ) {
      $this->client = $client;
      $this->entityTypeManager = $entityTypeManager;
      $this->languageManager = $languageManager;
  }

  public static function create(ContainerInterface $container) {
      return new static(
        $container->get('clashofclans_api.client'),
        $container->get('entity_type.manager'),
        $container->get('language_manager')
      );
  }

  /**
  * Get uid or create User if not exists.
  * Return uid.
  **/
  public function getUserId($tag) {
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
        $language = $this->languageManager->getCurrentLanguage()->getId();
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
        $user->set('langcode', $language);
        $user->set('preferred_langcode', $language);
        $user->set('preferred_admin_langcode', $language);
        $user->set('field_player_tag', $tag);
        $user->set('field_player_name', $data['name']);
        $user->activate();

        if (isset($data['legendStatistics']['bestSeason']['id'])) {
          $t = strtotime($data['legendStatistics']['bestSeason']['id']);
          $d = date('Y-m-d', $t);
          $user->set('field_best_season', $d);
          $user->set('field_best_season_rank', $data['legendStatistics']['bestSeason']['rank']);
          $user->set('field_best_season_trophies', $data['legendStatistics']['bestSeason']['trophies']);
          $user->set('field_best_trophies', $data['bestTrophies']);
          $user->set('field_legend_trophies', $data['legendStatistics']['legendTrophies']);
        }

        $user->addRole('gamer');

        // Save user account.
        $user->save();
        $id = $user->id();

      }
    }
    return $id;
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
        $uid = $this->getUserId($tag);
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
