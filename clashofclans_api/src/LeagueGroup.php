<?php
/**
*  Process data from League Group
*  Connect to drupal entity
**/
namespace Drupal\clashofclans_api;

use Drupal\clashofclans_api\Client;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\clashofclans_api\Clan;
use Drupal\clashofclans_api\War;
use Drupal\Core\Datetime\DrupalDateTime;

class LeagueGroup {
  protected $client;
  protected $entityTypeManager;
  protected $clan;
  protected $war;

  public function __construct(
    Client $client, EntityTypeManagerInterface $entityTypeManager, Clan $clan, War $war
  ) {
    $this->client = $client;
    $this->entityTypeManager = $entityTypeManager;
    $this->clan = $clan;
    $this->war = $war;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('clashofclans_api.client'),
      $container->get('entity_type.manager'),
      $container->get('clashofclans_api.clan'),
      $container->get('clashofclans_api.war')
    );
  }

  public function getData($tag) {
    $data = $this->fetchData($tag);
    if ($data) {
      $data = $this->processData($data);
    }
    return $data;
  }

  public function fetchData($tag) {
    $url = 'clans/'. urlencode($tag). '/currentwar/leaguegroup';
    $data = $this->client->get($url);
    if (isset($data['season'])) {
      return $data;
    }
  }

  public function getEntityId($tag, $data) { //$tag is clan tag;
    $season = $this->seasonToDate($data['season']);
    $storage = $this->entityTypeManager->getStorage('leaguegroup');
    $query = $storage->getQuery();
    $query -> condition('field_clan.entity:clashofclans_clan.clan_tag', $tag);
    $query -> condition('season', $season);
    $ids = $query->execute();
    if ($ids) {
      return current($ids);
    }
  }

  public function createEntity($data, $title) {
    $title .= '('. $data['season']. ')';
    $season = $this->seasonToDate($data['season']);
    $data = $this->processdata($data);

    $storage = $this->entityTypeManager->getStorage('leaguegroup');
    $entity = $storage->create([
      'title' => $title,
      'uid' => 1,
      //custom properties
      'season' => $season,
      'data' => \Drupal\Component\Serialization\Json::encode($data),
    ]);

    if (isset($data['clans'])) {
      $clans = $data['clans'];
      $target_ids = [];
      foreach ($clans as $clan) {
        $id = $this->clan->getEntityId($clan['tag']);
        $target_ids[] = $id;
      }
      $entity -> set('field_clan', $target_ids);
    }
    $entity->save();
    return $entity->id();
  }

  public function processData($data) {
    $data = $this->processClans($data);
    $data = $this->processWars($data);
    $data = $this->processWarResults($data);
    $data = $this->processClanStars($data, 'clan');
    $data = $this->processClanStars($data, 'opponent');
    $data = $this->sortClans($data);

    return $data;
  }

  public function processClans($data) {
    $items = [];
    foreach ($data['clans'] as $clan) {
      $tag = $clan['tag'];
      $items[$tag] = $clan;
      $items[$tag]['stars'] = 0;
    }
    $data['clans'] = $items;
    return $data;
  }

  public function processWars($data) {
    $wars = [];
    foreach ($data['rounds'] as $round) {
      $warTags = $round['warTags'];
      foreach ($warTags as $tag) {
        $war = $this->war->getData($tag);
        $wars[$tag] = $war;
      }
      if ($war['state'] == 'preparation') {
        break;
      }
    }
    $data['wars'] = $wars;
    return $data;
  }


  public function processWarResults($data) {
    $items = [];
    foreach ($data['wars'] as $warTag => $war) {
      if ($war['state'] == 'warEnded') {
        $clan = [
          'tag' => $war['clan']['tag'],
          'stars' => intval($war['clan']['stars']),
          'destructionPercentage' => floatval($war['clan']['destructionPercentage']),
        ];
        $opponent = [
          'tag' => $war['opponent']['tag'],
          'stars' => intval($war['opponent']['stars']),
          'destructionPercentage' => floatval($war['opponent']['destructionPercentage']),
        ];
        if ($clan['stars'] > $opponent['stars']) {
          $war['clan']['result'] = 'win';
          $war['opponent']['result'] = 'lose';
        }
        if ($clan['stars'] < $opponent['stars']) {
          $war['opponent']['result'] = 'win';
          $war['clan']['result'] = 'lose';
        }
        if ($clan['stars'] == $opponent['stars']) {
          if ($clan['destructionPercentage'] > $opponent['destructionPercentage']) {
            $war['clan']['result'] = 'win';
            $war['opponent']['result'] = 'lose';
          }
          if ($clan['destructionPercentage'] < $opponent['destructionPercentage']) {
            $war['opponent']['result'] = 'win';
            $war['clan']['result'] = 'lose';
          }
          if ($clan['destructionPercentage'] == $opponent['destructionPercentage']) {
            $war['clan']['result'] = 'tie';
            $war['opponent']['result'] = 'tie';
          }
        }
      } else {
        $war['clan']['result'] = '';
        $war['opponent']['result'] = '';
      }
      // finished caculate results.
      $items[$warTag] = $war;
    }
    $data['wars'] = $items;
    return $data;
  }

  public function processClanStars($data, $clan) {
    foreach ($data['wars'] as $war) {
      $tag = $war[$clan]['tag'];
      $data['clans'][$tag]['stars'] += intval($war[$clan]['stars']);
      if ($war[$clan]['result'] == 'win') {
        $data['clans'][$tag]['stars'] += 10;
      }
    }
    return $data;
  }

  public function sortClans($data) {
    uasort($data['clans'], [$this, 'cmpClanStars']);
    return $data;
  }

  public function cmpClanStars($a, $b){
    if ($a['stars'] == $b['stars']) {
      return 0;
    }
    return ($a['stars'] < $b['stars']) ? 1 : -1;
  }

  public function getCacheMaxAge() {
    return $this->client->getCacheMaxAge();
  }
  // protected function getClans($tag) {
  //   $data = $this->data[$tag];
  //   if (isset($data['clans'])) {
  //     $clans = $data['clans'];
  //     $tags = [];
  //     $entityClans = [];
  //     foreach ($clans as $clan) {
  //       $tag = $clan['tag'];
  //       $id = $this->clan->getEntityId($tag);
  //       $entityClans[$id] = [
  //         'tag' => $tag,
  //         'description' => $this->clan->getDescription($tag),
  //         'name' => $clan['name'],
  //         'clanLevel' => $clan['clanLevel'],
  //         'badgeUrls' => $clan['badgeUrls'],
  //       ];
  //     }
  //     return $entityClans;
  //   }
  // }

  public function seasonToDate($season) {
    $season .= ' UTC';
    $datetime = DrupalDateTime::createFromTimestamp(strtotime($season), 'UTC');
    return $datetime->format("Y-m-d");
  }
}
