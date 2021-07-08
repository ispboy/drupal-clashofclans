<?php

namespace Drupal\clashofclans_clan\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_api\Client;
use Drupal\clashofclans_api\Clan;
use Drupal\clashofclans_clan\Form\SearchForm;

/**
 * Returns responses for ClashOfClans Clan routes.
 */
class ClanController extends ControllerBase {

  private $client;
  private $clan;

  public function __construct(Client $client, Clan $clan) {
      $this->client = $client;
      $this->clan = $clan;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('clashofclans_api.client'),
      $container->get('clashofclans_api.clan'),
    );
  }

  public function setTitle($tag) {
    $name = $this->clan->getName($tag);
    if ($name) {
      return $name;
    } else {
      return $tag;
    }
  }

  /**
   * Builds the response.
   */
  public function tag($tag) {
    $id = $this->clan->getEntityId($tag);
    if ($id) {
      $route = 'entity.clashofclans_clan.canonical';
      return $this->redirect($route, ['clashofclans_clan' => $id]);
    }

    $build['content'] = [
      '#markup' => $this->t('Not found!'),
    ];

    return $build;

  }

  /**
  * Get or create/update
  **/
  public function getEntityId($tag) {
    $id = 0;
    $storage = $this->entityTypeManager()->getStorage('clashofclans_clan');
    $query = $storage->getQuery();
    $query -> condition('clan_tag', $tag);
    $ids = $query->execute();
    if ($ids) { //entity exists.
      $id = current($ids);
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
        $id = $entity->id();
      }
    }
    return $id;
  }

  /**
   * Builds the response.
   */
  public function memberList($tag, $nojs = 'nojs') {
    $build['content'] = [
      '#markup' => $this->t('Not found!'),
    ];

    $url = 'clans/' . urlencode($tag). '/members';
    $data = $this->client->get($url);

    if (isset($data['items'])) {
      $members = \Drupal\clashofclans_api\Members::getDetail($data['items'], $this->client, 15);
      if ($members) {
        $fields = [
          'Rank' => 'clanRank',
          'league' => 'league',
          'expLevel' => 'expLevel',
          'Name'  => 'name',
          // 'role' => 'role',
          // 'donations' => 'donations',
          // 'Received' => 'donationsReceived',
          'attackWins' => 'attackWins',
          'defenseWins' => 'defenseWins',
          'legendTrophies' => 'legendTrophies',
          'Best season' => 'bestSeason',
          'Previous season' => 'previousSeason',
          'versusTrophies'  => 'versusTrophies',
          'trophies'  => 'trophies',
        ];
        $build['content'] = \Drupal\clashofclans_api\Render::players($members, $fields);
        $build['#cache']['max-age'] = $this->client->getCacheMaxAge();
      }
    }

    // Determine whether the request is coming from AJAX or not.
    if ($nojs == 'ajax') {
      $response = new \Drupal\Core\Ajax\AjaxResponse();
      $response->addCommand(new \Drupal\Core\Ajax\ReplaceCommand('#content .clashofclans-players-table', $build['content']));
      return $response;
    }

    return $build;
  }

  /**
   * Builds the response.
   */
  public function warLog($tag) {
    $url = 'clans/'. urlencode($tag). '/warlog';
    $data = $this->client->get($url);
    if (isset($data['items'])) {
      $build['content'] = [
       '#theme' => 'clashofclans_clan_warlog',
       '#items' => $data['items'],
      ];
    } else {
      $build['content'] = ['#markup' => $this->t('No content.')];
    }

    $build['#cache']['max-age'] = $this->client->getCacheMaxAge()*30;
    return $build;
  }

  /**
   * Builds the response.
   */
  public function currentWar($tag) {
    $build['content'] = [
      '#markup' => $this->t('No content.'),
    ];
    $url = 'clans/'. urlencode($tag). '/currentwar';
    $data = $this->client->get($url);
// dpm(array_keys($data['clan']));
    if ($data) {
      $build['#cache']['max-age'] = $this->client->getCacheMaxAge();
      if ($data['state'] == 'notInWar') {
        $build['content'] = [
          '#markup' => $this->t('Not in war.'),
        ];
      } else {
        $war = new \Drupal\clashofclans_api\CurrentWar($data);
        // dpm($war->getPlayers());
        $build['content'] = [
         '#theme' => 'clashofclans_clan_currentwar',
         '#war' => $war->getData(),
         '#players' => $war->getPlayers(),
        ];
      }
    }

    $build['#cache']['max-age'] = $this->client->getCacheMaxAge()*5;
    return $build;
   }

   public function getLeagueGroup($tag) {
     $url = 'clans/'. urlencode($tag). '/currentwar/leaguegroup';
     $data = $this->client->get($url);
     $clans =[];
     foreach ($data['clans'] as $clan) {
       $clans[$clan['tag']] = $clan;
       $clans[$clan['tag']]['stars'] = 0;
       $clans[$clan['tag']]['destructionPercentage'] = 0;
     }
     $data['clans'] = $clans;  //assign clan tag as key

     foreach ($data['rounds'] as $key_round => $round) {
       $data['rounds'][$key_round]['warData'] = [];
       foreach ($round['warTags'] as $key_war => $war_tag) {
         if ($war_tag != '#0') {
           $url = 'clanwarleagues/wars/'. urlencode($war_tag);
           $data['rounds'][$key_round]['warData'][$war_tag] = $this->client->get($url);
           $this->processLeagueClan($data['rounds'][$key_round]['warData'][$war_tag], $data['clans']);
         }
       }
     }

     uasort($data['clans'], [$this, 'cmp']);
     return $data;
   }

  protected function processLeagueClan($war, &$clans) {
    $clan = [
      'stars' => intval($war['clan']['stars']),
      'destructionPercentage' => floatval($war['clan']['destructionPercentage']) * 15,
    ];
    $opponent = [
      'stars' => intval($war['opponent']['stars']),
      'destructionPercentage' => floatval($war['opponent']['destructionPercentage']) * 15,
    ];

    $clans[$war['clan']['tag']]['stars'] += $clan['stars'];
    $clans[$war['opponent']['tag']]['stars'] += $opponent['stars'];
    $clans[$war['clan']['tag']]['destructionPercentage'] += $clan['destructionPercentage'];
    $clans[$war['opponent']['tag']]['destructionPercentage'] += $opponent['destructionPercentage'];

    if (isset($war['state']) && $war['state'] == 'warEnded') {
      if ($clan['stars'] > $opponent['stars']) {
        $clans[$war['clan']['tag']]['stars'] += 10;
      } elseif ($clan['stars'] < $opponent['stars']) {
        $clans[$war['opponent']['tag']]['stars'] += 10;
      } elseif ($clan['destructionPercentage'] > $opponent['destructionPercentage']) {
        $clans[$war['clan']['tag']]['stars'] += 10;
      } elseif ($clan['destructionPercentage'] < $opponent['destructionPercentage']) {
        $clans[$war['opponent']['tag']]['stars'] += 10;
      }
    }
  }

  public function cmp($a, $b){
    if ($a['stars'] == $b['stars']) {
      if ($a['destructionPercentage'] == $b['destructionPercentage']) {
        return 0;
      }
      return ($a['destructionPercentage'] < $b['destructionPercentage']) ? 1 : -1;
    }
    return ($a['stars'] < $b['stars']) ? 1 : -1;
  }

   /**
  * Builds the response.
  */
  public function renderLeagueGroup($tag) {
      $data = $this->getLeagueGroup($tag);
      $build['content'] = [
        '#theme' => 'clashofclans_clan_leaguegroup',
        '#data' => $data,
      ];

      return $build;
    }

  /**
  * Builds the response.
  */
  public function leagueWar($tag) {
    $url = 'clanwarleagues/wars/'. urlencode($tag);
    $data = $this->client->get($url);

    $build['content'] = [
      '#theme' => 'clashofclans_clan_leaguewar',
      '#war' => $data,
    ];

    return $build;
  }

  /**
   * Builds the response.
   */
  public function search(\Symfony\Component\HttpFoundation\Request $request) {

    $build['search_form'] = $this->formBuilder()->getForm(SearchForm::class);

    $query_string = $request->getQueryString();
    if ($query_string) {
      $url = 'clans?'. $query_string. '&limit=50';
      $data = $this->client->get($url);
      if ($data) {
        $fields = [
          'Badge' => 'badge',
          'Name'  => 'name',
          'Type' => 'type',
          'requiredTH' => 'requiredTownhallLevel',
          'requiredTrophies' => 'requiredTrophies',
          'requiredVersus' => 'requiredVersusTrophies',
          'members'  => 'members',
          'Location'  => 'location',
          'isWarLogPublic' => 'isWarLogPublic',
          'clanPoints'  => 'clanPoints',
        ];
        $build['content'] = \Drupal\clashofclans_api\Render::clans($data['items'], $fields);
      } else {
        $build['content'] = [
          '#markup' => $this->t('No results.'),
        ];
      }
    }

    $build['#cache']['contexts'] = ['url.query_args'];
    return $build;
  }
}
