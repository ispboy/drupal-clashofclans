<?php
/**
*  Process data from War
*  Connect to drupal entity
**/
namespace Drupal\clashofclans_war;

use Drupal\clashofclans_api\GuzzleCache;
use Drupal\clashofclans_clan\Clan;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Datetime\DrupalDateTime;

class War {
  private $client;
  private $clan;
  private $entityTypeManager;

  public function __construct(GuzzleCache $client, Clan $clan, EntityTypeManagerInterface $entityTypeManager) {
    $this->client = $client;
    $this->clan = $clan;
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('clashofclans_api.guzzle_cache'),
      $container->get('clashofclans_clan.clan'),
      $container->get('entity_type.manager'),
    );
  }

  /**
  * response to controller tag();
  **/
  public function tag($tag) {
    $result = [];
    $id = $this->getEntityId($tag);
    if ($id) {
      $result['id'] = $id;
    } else {
      $url = 'clanwarleagues/wars/'. $tag;
      $data = $this->client->getData($url);
      if (isset($data['state']) && $data['state'] == 'warEnded') {
        $entity = $this->createEntity($data, $tag);
        if ($entity) $result['id'] = $entity->id();
      }
      $data = $this->preprocessData($data);
      $result['data'] = $data;
    }

    return $result;
  }

  /**
  * Get war data from api or entity.
  **/
  public function getData($tag) {
    $result = $this->tag($tag);
    if (isset($result['data'])) {
      return $result['data'];
    }
    if (isset($result['id'])) {
      $storage = $this->entityTypeManager->getStorage('clashofclans_war');
      $entity = $storage->load($result['id']);
      $json = $entity->get('field_data')->value;
      $data = \Drupal\Component\Serialization\Json::decode($json);
      $data = $this->preprocessData($data);
      return $data;
    }
  }

  /**
  * Get EntityId
  **/
  public function getEntityId($tag) {
    $storage = $this->entityTypeManager->getStorage('clashofclans_war');
    $query = $storage->getQuery();
    $query -> condition('field_war_tag', $tag);
    $ids = $query->execute();
    if ($ids) { //entity exists.
      $id = current($ids);
      return $id;
    }
  }

  public function createEntity($data, $war_tag) {
    $storage = $this->entityTypeManager->getStorage('clashofclans_war');
    if (isset($data['startTime'])) {
      $start_time = $this->convertTime($data['startTime']);
      $json = \Drupal\Component\Serialization\Json::encode($data);

      $entity = $storage->create([
        'bundle' => 'league_war',
        'title' => $this->getTitle($data),
        'team_size' => $data['teamSize'],
        'start_time' => $start_time,
        'field_war_tag' => $war_tag,
        'field_data' => $json,
        'field_clan' => $this->getClanTarget($data),
        'uid' => 1,
      ]);
      $entity->save();
      \Drupal::messenger()->addMessage('War data saved.');
      return $entity;
    }
  }

  public function getClanTarget($data) {
    $target_ids = [];
    if (isset($data['clan']['tag'])) $target_ids[] = ['target_id' => $this->clan->getEntityId($data['clan']['tag'])];
    if (isset($data['opponent']['tag'])) $target_ids[] = ['target_id' => $this->clan->getEntityId($data['opponent']['tag'])];
    return $target_ids;
  }

  public function getTitle($data) {
    $clans = [];
    if (isset($data['clan']['name'])) $clans[] = $data['clan']['name'];
    if (isset($data['opponent']['name'])) $clans[] = $data['opponent']['name'];
    if (isset($data['startTime'])) $start_time = $this->convertTime($data['startTime']);
    if ($clans) {
      $title = implode(' vs ', $clans). ' ('. $start_time. ')';
    } else {
      $title = 'No title';
    }
    return $title;
  }

  /**
  * process data.
  */
  public function preprocessData($data) {
    $data['clan']['averageDestruction'] = $this->setAverageDestruction($data['clan'], $data['teamSize']);
    $data['opponent']['averageDestruction'] = $this->setAverageDestruction($data['opponent'], $data['teamSize']);
    $data = $this->setPlayers($data);
    $data = $this->setEvents($data);
    return $data;
  }

  /**
  * process players.
  */
  public function setPlayers($data) {
    $items = [];
    foreach ($data['clan']['members'] as $item) {
      $item['clan'] = 'clan';
      $items[$item['tag']] = $item;
    }
    foreach ($data['opponent']['members'] as $item) {
      $item['clan'] = 'opponent';
      $items[$item['tag']] = $item;
    }
    $data['players'] = $items;
    return $data;
  }

  /**
  * process events.
  */
  public function setEvents($data) {
    $events = [];
    $players = $data['players'];
    foreach ($players as $player) {
      if (isset($player['attacks'])) {
        foreach ($player['attacks'] as $attack) {
          $attack['clan'] = $player['clan'];
          $events[] = $attack;
        }
      }
    }
    $orders = \array_column($events, 'order');
    array_multisort($orders, SORT_DESC, $events);
    $data['events'] = $events;
    return $data;
  }

  /**
  * process events.
  */
  public function setAverageDestruction($clan, $team_size) {
    $attacks = intval($clan['attacks']);
    if ($attacks > 0) {
      $percentage = \floatval($clan['destructionPercentage']);
      $size = \intval($team_size);
      $result = $percentage * $size / $attacks;
      return $result;
    } else {
      return 0;
    }
  }

  public function convertTime($time) {
    $time_str = str_replace('.000Z', ' UTC', $time);
    $datetime = DrupalDateTime::createFromTimestamp(strtotime($time_str), 'UTC');
    return $datetime->format("Y-m-d\TH:i:s");
  }

  public function getClient() {
    if (isset($this->client)) return $this->client;
  }

}
