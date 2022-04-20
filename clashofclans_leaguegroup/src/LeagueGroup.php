<?php
namespace Drupal\clashofclans_leaguegroup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\clashofclans_api\GuzzleCache;
use Drupal\clashofclans_war\War;
use Drupal\clashofclans_clan\Clan;


class LeagueGroup {
  public $client;
  protected $entityTypeManager;
  private $war;
  private $clan;

  public function __construct(
    GuzzleCache $client,
    EntityTypeManagerInterface $entityTypeManager,
    War $war,
    Clan $clan,
  ) {
      $this->client = $client;
      $this->entityTypeManager = $entityTypeManager;
      $this->war = $war;
      $this->clan = $clan;
  }

  public static function create(ContainerInterface $container) {
      return new static(
        $container->get('clashofclans_api.guzzle_cache'),
        $container->get('entity_type.manager'),
        $container->get('clashofclans_war.war'),
        $container->get('clashofclans_clan.clan'),
      );
  }

  public function currentWar($entity) {
    if (isset($entity->tag->value)) {
      $tag = $entity->tag->value;
      $url = 'clans/'. $tag. '/currentwar/leaguegroup';
      $data = $this->client->getData($url);
      if ($data) {
        if(isset($data['state']) && isset($data['season']) && $data['state'] == 'ended') {
          $id = $this->getEntityId($data, $tag);
          if (!$id) {
            $id = $this->createEntity($data, $entity);
          }
        }
        $warLeague = $entity->get('field_warleague')->first()->get('entity')->getValue();
        if ($warLeague) {
          $data['warLeague'] = $warLeague->get('field_image')->view('icon');
        }
        $data = $this->processData($data);

        return $data;
      }
    }
  }

  /**
  * Get EntityId
  **/
  public function getEntityId($data, $tag) {
    $season = $this->seasonToDate($data['season']);
    $storage = $this->entityTypeManager->getStorage('clashofclans_leaguegroup');
    $query = $storage->getQuery();
    $query -> condition('field_clan.entity:clashofclans_clan.tag', $tag);
    $query -> condition('field_date', $season);
    $ids = $query->execute();
    if ($ids) {
      return current($ids);
    }
  }

  public function createEntity($data, $clan) {
    $title = $clan->label(). ' ('. $data['season']. ')';
    $warLeague = $clan->field_warleague->target_id;
    $date = $this->seasonToDate($data['season']);

    $storage = $this->entityTypeManager->getStorage('clashofclans_leaguegroup');
    $entity = $storage->create([
      'title' => $title,
      'uid' => 1,
      //custom properties
      'field_date' => $date,
      'field_warleague' => ['target_id' => $warLeague],
      'field_data' => \Drupal\Component\Serialization\Json::encode($data),
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

  public function seasonToDate($season) {
    $season .= ' UTC';
    $datetime = \Drupal\Core\Datetime\DrupalDateTime::createFromTimestamp(strtotime($season), 'UTC');
    return $datetime->format("Y-m-d");
  }

  public function processData($data) {
    $data = $this->processClans($data);
    $data = $this->processWars($data);
    $data = $this->processWarResults($data);
    $data = $this->processClanStars($data, 'clan');
    $data = $this->processClanStars($data, 'opponent');
    $data = $this->processPlayerStars($data, 'clan', 'opponent');
    $data = $this->processPlayerStars($data, 'opponent', 'clan');
    $data = $this->sortClans($data);

    return $data;
  }

  public function processClans($data) {
    $clans = [];
    foreach ($data['clans'] as $clan) {
      $tag = $clan['tag'];
      $clans[$tag] = $clan;
      $clans[$tag]['stars'] = 0;
      $clans[$tag]['destructionPercentage'] = 0;
      $members = [];
      foreach ($clan['members'] as $player) {
        $player_tag = $player['tag'];
        $members[$player_tag] = $player;
        $members[$player_tag]['stars'] = 0;
        $members[$player_tag]['destructionPercentage'] = 0;
        $members[$player_tag]['lost'] = 0;
        $members[$player_tag]['attacks'] = 0;
        $members[$player_tag]['attend'] = 0;
      }
      $clans[$tag]['members'] = $members;
    }
    $data['clans'] = $clans;
    return $data;
  }

  public function processWars($data) {
    $wars = [];
    foreach ($data['rounds'] as $round) {
      $warTags = $round['warTags'];
      foreach ($warTags as $tag) {
        $war = $this->war->getData($tag);
        if ($war) {
          $wars[$tag] = $war;
        }
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
      $data['clans'][$tag]['destructionPercentage'] += floatval($war[$clan]['destructionPercentage'] * intval($war['teamSize']));
      if ($war[$clan]['result'] == 'win') {
        $data['clans'][$tag]['stars'] += 10;
      }
    }
    return $data;
  }

  public function processPlayerStars($data, $clan = 'clan', $opponent = 'opponent') {
    foreach ($data['wars'] as $war) {
      $clan_tag = $war[$clan]['tag'];
      $opponent_tag = $war[$opponent]['tag'];
      foreach ($war[$opponent]['members'] as $player) {
        if (isset($war['state']) && $war['state'] == 'warEnded') {
          $player_tag = $player['tag'];
          $data['clans'][$opponent_tag]['members'][$player_tag]['attend'] ++;
          if (isset($player['attacks'])) {
            $data['clans'][$opponent_tag]['members'][$player_tag]['attacks'] += count($player['attacks']);
          }

          if (isset($player['bestOpponentAttack'])) {
            $player_tag = $player['bestOpponentAttack']['attackerTag'];
            $stars = $player['bestOpponentAttack']['stars'];
            $destructionPercentage = $player['bestOpponentAttack']['destructionPercentage'];
            $data['clans'][$clan_tag]['members'][$player_tag]['stars'] += intval($stars);
            $data['clans'][$clan_tag]['members'][$player_tag]['destructionPercentage'] += intval($destructionPercentage);

            $player_tag = $player['bestOpponentAttack']['defenderTag'];
            $data['clans'][$opponent_tag]['members'][$player_tag]['lost'] += intval($stars);

          }
        }

      }
    }
    return $data;
  }

  public function sortClans($data) {
    uasort($data['clans'], [$this, 'cmpClanStars']);
    foreach ($data['clans'] as $key => $clan) {
      uasort($data['clans'][$key]['members'], [$this, 'cmpClanStars']);
    }
    return $data;
  }

  public function cmpClanStars($a, $b){
    if ($a['stars'] == $b['stars']) {
      if ($a['destructionPercentage'] == $b['destructionPercentage']) {
        return 0;
      }
      return ($a['destructionPercentage'] < $b['destructionPercentage']) ? 1 : -1;
    }
    return ($a['stars'] < $b['stars']) ? 1 : -1;
  }
}
