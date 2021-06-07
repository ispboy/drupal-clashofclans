<?php

namespace Drupal\clashofclans_clan\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\clashofclans_clan\Form\SearchForm;

/**
 * Returns responses for ClashOfClans Clan routes.
 */
class ClanController extends ControllerBase {

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

  public function setTitle($tag) {
    $title = $tag;  //provide default title, if not found.
    $url = 'clans/'. urlencode($tag);
    $data = $this->client->getArray($url);

    if (isset($data['name'])) {
      $title = $data['name'];
    }
    return $title;
  }

  /**
   * Builds the response.
   */
  public function tag($tag) {
    $url = 'clans/'. urlencode($tag);
    $data = $this->client->getArray($url);

    if (!isset($data['name'])) {
      $build['content'] = [
        '#markup' => $this->t('Not found!'),
      ];

      return $build;
    }

    $build['content'] = [
      '#theme' => 'clashofclans_clan_tag',
      '#clan' => $data,
    ];

    if (isset($data['location'])) {
      $location = $this->client->linkLocation($data['location']['name'], $data['location']['id']);
      $build['content']['#location'] = $location;
    }

    if (isset($data['isWarLogPublic']) && $data['isWarLogPublic']) {
      $title = $this->t('Current war');
      $build['content']['#current_war'] = Link::fromTextAndUrl($title, Url::fromRoute('clashofclans_clan.tag.currentwar', ['tag' => $tag]))->toString();

      $title = $this->t('League group');
      $build['content']['#league_group'] = Link::fromTextAndUrl($title, Url::fromRoute('clashofclans_clan.tag.leaguegroup', ['tag' => $tag]))->toString();
    }

    if (isset($data['memberList'])) {
      $fields = [
        'Rank' => 'clanRank',
        'league' => 'league',
        'expLevel' => 'expLevel',
        'Name'  => 'name',
        'role' => 'role',
        'donations' => 'donations',
        'Received' => 'donationsReceived',
        'versusTrophies'  => 'versusTrophies',
        'trophies'  => 'trophies',
      ];
      $build['content']['#member_list']= $this->client->buildPlayers($data['memberList'], $fields);

    }

    return $build;

  }

  /**
   * Builds the response.
   */
  public function currentWar($tag) {
     $url = 'clans/'. urlencode($tag). '/currentwar';
     $data = $this->client->getArray($url);
     $state = $data['state'];

     $build['content'] = [
       '#markup' => $this->t($state),
     ];

     return $build;
   }

   public function getLeagueGroup($tag) {
     $url = 'clans/'. urlencode($tag). '/currentwar/leaguegroup';
     $data = $this->client->getArray($url);
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
           $data['rounds'][$key_round]['warData'][$war_tag] = $this->client->getArray($url);
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
    $data = $this->client->getArray($url);

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

    $query_string = $request->getQueryString();
    $url = 'clans?'. $query_string;
    $data = $this->client->getArray($url);
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

    $build['search_form'] = $this->formBuilder()->getForm(SearchForm::class);
    $build['content'] = $this->client->buildClans($data['items'], $fields);

    return $build;
  }
}
