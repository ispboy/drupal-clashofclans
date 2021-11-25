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
  * clan's currentwar;
  * @param $entity: clan entity
  **/
  public function currentWar($entity) {
    if (isset($entity->tag->value)) {
      $tag = $entity->tag->value;
      $url = 'clans/'. $tag. '/currentwar';
      $data = $this->client->getData($url);
      if ($data) {
        $data = $this->preprocessData($data);
        return $data;
      }
    }
  }

  /**
  * response to controller tag();
  **/
  public function tag($tag) {
    $result = [];
    $id = $this->getLeagueWarId($tag);
    if ($id) {
      $result['id'] = $id;
    } else {
      $url = 'clanwarleagues/wars/'. $tag;
      $data = $this->client->getData($url);
      if (isset($data['state']) && $data['state'] == 'warEnded') {
        $entity = $this->createLeagueWar($data, $tag);
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
  public function getLeagueWarId($tag) {
    $storage = $this->entityTypeManager->getStorage('clashofclans_war');
    $query = $storage->getQuery();
    $query -> condition('field_war_tag', $tag);
    $ids = $query->execute();
    if ($ids) { //entity exists.
      $id = current($ids);
      return $id;
    }
  }

  public function createLeagueWar($data, $war_tag) {
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
    if (isset($data['clan']['members'])) {
      $data['clan']['averageDestruction'] = $this->getAverageDestruction('clan', 'opponent', $data);
      $data['opponent']['averageDestruction'] = $this->getAverageDestruction('opponent', 'clan', $data);
      $data['players'] = $this->getPlayers('clan', 'opponent', $data);
      $data['players'] += $this->getPlayers('opponent', 'clan', $data);
      $data = $this->getEvents($data);
      $data['clan']['bestStars'] = $this->getBestStars('clan', $data);
      $data['opponent']['bestStars'] = $this->getBestStars('opponent', $data);
    }
    return $data;
  }

  /**
  * process players.
  */
  public function getPlayers($clan, $opponent, $data) {
    $items = [];  //initial players
    foreach ($data[$clan]['members'] as $item) {
      $tag = $item['tag']; //player tag
      $item['clan'] = $clan;
      $item['bestStars'] = [0, 0, 0, 0];
      $items[$tag] = $item;
    }

    //calculate player's best attack stars.
    foreach ($data[$opponent]['members'] as $member) {
      if (isset($member['bestOpponentAttack'])) {
        $bestOpponentAttack = $member['bestOpponentAttack'];
        $tag = $bestOpponentAttack['attackerTag'];
        $key = intval($bestOpponentAttack['stars']);
        $items[$tag]['bestStars'][$key] ++;
      }
    }

    return $items;
  }

  /**
  * process events.
  */
  public function getBestStars($clan, $data) {
    $items = [0, 0, 0, 0];
    $players = $data['players'];
    $members = \array_filter($players, function($player) use($clan) {
      if ($player['clan'] == $clan) return TRUE;
    });
    $bestStars = \array_column($members, 'bestStars');
    for ($i = 0; $i < count($items); $i++) {
      $items[$i] = \array_sum(\array_column($bestStars, $i));
    }
    return $items;
  }

  /**
  * process events.
  */
  public function getEvents($data) {
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
  public function getAverageDestruction($clan, $opponent, $data) {
    $members = $data[$opponent]['members'];
    $bestOpponentAttack = array_filter($members, function($player) {
      if (\array_key_exists('bestOpponentAttack', $player)) return TRUE;
    });
    $count = count($bestOpponentAttack);

    if ($count > 0) {
      $percentage = \floatval($data[$clan]['destructionPercentage']);
      $size = \intval($data['teamSize']);
      $result = $percentage * $size / $count;
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
