<?php

namespace Drupal\clashofclans_player\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans\ClashofclansClient;

/**
 * Returns responses for ClashOfClans Player routes.
 */
class PlayerController extends ControllerBase {
  private $client;

  public function __construct(\Drupal\clashofclans_api\Client $client)
  {
      $this->client = $client;
  }

  public static function create(ContainerInterface $container)
  {
      $client = $container->get('clashofclans_api.client');
      return new static($client);
  }

  public function userTitle(\Drupal\user\UserInterface $user = NULL) {
    $result = '';
    if ($user) {
      $name = $user->get('field_player_name')->getString();
      if ($name) {
        $result = [
          '#markup' => $name,
          '#allowed_tags' => \Drupal\Component\Utility\Xss::getHtmlTagList(),
        ];
      } else {
        $result = [
          '#markup' => $user->getDisplayName(),
          '#allowed_tags' => \Drupal\Component\Utility\Xss::getHtmlTagList(),
        ];
      }
    }

    return $result;
  }

  /**
   * Builds the response.
   */
  public function tag($tag) {
    $route = 'entity.user.canonical';

    $id = $this->getUserId($tag);
    if ($id) {
      return $this->redirect($route, ['user' => $id]);
    }

    $build['content'] = [
      '#markup' => $this->t('Not found!'),
    ];

    return $build;

  }

  /**
  * Get or create/update User.
  * Return uid.
  **/
  public function getUserId($tag) {
    $id = 0;
    $storage = $this->entityTypeManager()->getStorage('user');
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
        $language = $this->languageManager()->getCurrentLanguage()->getId();
        $user = \Drupal\user\Entity\User::create();

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
}
