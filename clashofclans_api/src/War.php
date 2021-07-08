<?php
/**
*  Process data from War
*  Connect to drupal entity
**/
namespace Drupal\clashofclans_api;

use Drupal\clashofclans_api\Client;
use Drupal\clashofclans_api\Clan;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class War {
  private $client;
  private $clan;
  private $entityTypeManager;

  public function __construct(Client $client, Clan $clan, EntityTypeManagerInterface $entityTypeManager) {
    $this->client = $client;
    $this->clan = $clan;
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('clashofclans_api.client'),
      $container->get('clashofclans_api.clan'),
      $container->get('entity_type.manager'),
    );
  }

  public function getTitle($tag, $type='league_war') {
    $data = $this->getData($tag, $type);
    return $this->buildTitle($data);
  }

  public function getData($tag, $type='league_war') {
    if ($type == 'league_war') {
      $entity = $this->getEntity($tag, $type);
      if ($entity) {
        $json = $entity->get('data')->getString();
        if ($json) return \Drupal\Component\Serialization\Json::decode($json);
      } else {
        $data = $this->fetchData($tag, $type);
        if ($data) {
          $data = $this->processData($data, $type);
          if ($data['state'] == 'warEnded') {
            $this->createEntity($tag, $type, $data);
          }
        }
        return $data;
      }
    }

    if ($type == 'clan_war') {
      $data = $this->fetchData($tag, $type);
      if ($data) {
        $data = $this->processData($data, $type);
      }      
      return $data;
    }
  }

  public function fetchData($tag, $type) {
    $urls = [
      'clan_war' => 'clans/'. urlencode($tag). '/currentwar',
      'league_war' => 'clanwarleagues/wars/'. urlencode($tag),
    ];
    $url = $urls[$type];
    $data = $this->client->get($url);
    if (isset($data['state'])) {
      return $data;
    }
  }

  public function getEntity($tag, $type, $end_time = NULL) {
    $storage = $this->entityTypeManager->getStorage('clashofclans_war');
    $query = $storage->getQuery();
    $query -> condition('bundle', $type);
    $query -> condition('tag', $tag);
    if ($type == 'clan_war') {
      $query -> condition('end_time', $end_time);
    }
    $ids = $query->execute();
    if ($ids) {
      $entity = $storage->load(current($ids));
      return $entity;
    }
  }

  public function createEntity($tag, $type, $data) {
    $storage = $this->entityTypeManager->getStorage('clashofclans_war');
    $title = $this->buildTitle($data);
    $clan_id = $this->clan->getEntityId($data['clan']['tag']);
    $opponent_id = $this->clan->getEntityId($data['opponent']['tag']);
    $end_time = $this->client->strToDatetime($data['endTime']);
    $entity = $storage->create([
      'title' => $title,
      'bundle' => $type,
      'uid' => 1,
      //custom properties
      'team_size' => $data['teamSize'],
      'data' => \Drupal\Component\Serialization\Json::encode($data),
      'end_time' => $end_time,
      'tag' => $tag,
      'clan' => ['target_id' => $clan_id],
      'opponent' => ['target_id' => $opponent_id],
    ]);
    $entity->save();
  }

  public function buildTitle($data) {
    if (isset($data['clan']['name']) && isset($data['opponent']['name'])) {
      $title = implode(' ', [
        $data['clan']['name'], 'vs', $data['opponent']['name']
      ]);
      return $title;
    }
  }

  public function getCacheMaxAge() {
    return $this->client->getCacheMaxAge();
  }

  public function processData($data, $type) {
    $data['clan'] = $this->processRemainAttacks($data['clan'], $data['teamSize'], $type);
    $data['opponent'] = $this->processRemainAttacks($data['opponent'], $data['teamSize'], $type);

    if ($data['state'] != 'preparation') {
      $data = $this->processMembers($data, 'clan');
      $data = $this->processMembers($data, 'opponent');
      $data = $this->processOpponent($data, 'clan', 'opponent');
      $data = $this->processOpponent($data, 'opponent', 'clan');
      if (isset($data['events'])) {
        krsort($data['events']);
      }
    }

    return $data;
  }

  public function processRemainAttacks($clan, $team_size, $type) {
    if ($type == 'clan_war') {
      $team_size = $team_size * 2;
    }
    $remain = $team_size - $clan['attacks'];
    $clan['remainAttacks'] = $remain;
    return $clan;
  }

  /**
  * fetch all members to players
  **/
  public function processMembers($data, $clan_type = 'clan') {
    $clan = $data[$clan_type];
    $clan['bestPlayers'] = [];
    foreach ($clan['members'] as $member) {

      $tag = $member['tag'];
      $data['players'][$tag] = $member;

      //initial bestPlayers' stars
      $clan['bestPlayers'][$tag] = [
        'name' => $member['name'],
        'stars' => [0, 0, 0, 0],
      ];

      //log the war events
      if (isset($member['attacks'])) {
        foreach ($member['attacks'] as $event) {
          $order = $event['order'];
          $data['events'][$order] = $event;
          $data['events'][$order]['type'] = $clan_type;
          if ($clan_type == 'clan') {
            $data['events'][$order]['leftTag'] = $event['attackerTag'];
            $data['events'][$order]['rightTag'] = $event['defenderTag'];
          } elseif ($clan_type == 'opponent') {
            $data['events'][$order]['leftTag'] = $event['defenderTag'];
            $data['events'][$order]['rightTag'] = $event['attackerTag'];
          }
        }
      }
    }
    uasort($clan['members'], [$this, 'cmpMapPosition']);
    $data[$clan_type] = $clan;
    return $data;
  }

  public function processOpponent($data, $clan_type = 'clan', $opponent_type = 'opponent') {
    $data[$clan_type]['bestAttacks'] = [0, 0, 0, 0];  // index. star
    foreach ($data[$opponent_type]['members'] as $member) {
      if (isset($member['bestOpponentAttack'])) {
        $stars = $member['bestOpponentAttack']['stars'];
        $tag = $member['bestOpponentAttack']['attackerTag'];
        $i = intval($stars);
        $data[$clan_type]['bestAttacks'][$i] ++;
        $data[$clan_type]['bestPlayers'][$tag]['stars'][$i] ++;
      }
    }
    uasort($data[$clan_type]['bestPlayers'], [$this, 'cmpPlayerStars']);
    return $data;
  }

  public function cmpMapPosition($a, $b){
    if ($a['mapPosition'] == $b['mapPosition']) {
      return 0;
    }
    return ($a['mapPosition'] > $b['mapPosition']) ? 1 : -1;
  }

  public function cmpPlayerStars($a, $b){
    if ($a['stars'][3] == $b['stars'][3]) {
      return 0;
    }
    return ($a['stars'][3] < $b['stars'][3]) ? 1 : -1;
  }
}
