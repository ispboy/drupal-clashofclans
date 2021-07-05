<?php
namespace Drupal\clashofclans_leaguegroup;

class LeagueGroup {

  private $state = '';
  private $season = '';
  private $clans = [];
  private $players = [];
  private $warDatas = [];
  private $rounds = [];
  /**
   * Class constructor.
   */
  public function __construct($data) {
    $this->state = isset($data['state'])? $data['state']: '';
    $this->season = isset($data['season'])? $data['season']: '';
    $this->clans = $this->parseClans($data);
    $this->players = $this->parsePlayers($data);
    $this->warDatas = $this->parseWarDatas($data);
    $this->rounds = $this->parseRounds($data);
  }

  protected function parseClans($data) {
    $items = [];
    foreach ($data['clans'] as $clan) {
      $tag = $clan['tag'];
      $items[$tag] = $clan;
      $items[$tag]['stars'] = 0;
      $items[$tag]['destructionPercentage'] = 0;
    }
    return $items;
  }

  protected function parsePlayers($data) {
    $items = [];
    foreach ($data['clans'] as $clan) {
      foreach ($clan['members'] as $member) {
        $tag = $member['tag'];
        $items[$tag] = [
          'name' => $member['name'],
          'clanTag' => $clan['tag'],
          'townHallLevel' => $member['townHallLevel'],
        ];
      }
    }
    return $items;
  }

  protected function parseWarDatas($data) {
    $items = [];
    $war_tags = [];
    foreach ($data['rounds'] as $round) {
      foreach ($round['warTags'] as $tag) {
        $war_tags[] = $tag;
      }
    }

    if ($war_tags) {
      $entity = \Drupal::entityTypeManager()->getStorage('clashofclans_war');
      $query = $entity->getQuery();
      $ids = $query->condition('status', 1)
       ->condition('field_war_tag', $war_tags, 'in')
       ->execute();
      $nodes = $entity->loadMultiple($ids);
      foreach ($nodes as $node) {
        $tag = $node->get('field_war_tag')->getString();
        $json = $node->get('field_data')->getString();
        if ($json) {
          $items[$tag] = \Drupal\Component\Serialization\Json::decode($json);
          // $items[$tag] = $tag;
        }
      }
    }
    return $items;
  }

  protected function parseRounds($data) {
    $items = [];
    $wars = $this->getWarDatas();
    foreach ($data['rounds'] as $key => $round) {
      foreach ($round['warTags'] as $tag) {
        $war = $wars[$tag];
        $items[$key]['warDatas'][$tag] = $war;
      }
      // if (isset($wars[$tag]['state'])) {
      //   $items[$key]['state'] = $wars[$tag]['state'];
      // }
    }
    return $items;
  }

  public function getState() {
    return $this->state;
  }

  public function getSeason() {
    return $this->season;
  }

  public function getClans() {
    return $this->clans;
  }

  public function getPlayers() {
    return $this->players;
  }

  public function getWarDatas() {
    return $this->warDatas;
  }

  public function getRounds() {
    return $this->rounds;
  }

  public function execute() {
    $clans = $this->getClans();
    $players = $this->getPlayers();
    $rounds = $this->getRounds();
    $warDatas = $this->getWarDatas();
    // dpm(array_keys($clans['#C00RJP']));
    // dpm($warDatas['#2J2P0LP09']);

    foreach ($warDatas as $war) {
      $this->sumClans($war, $this->clans);
    }
    uasort($this->clans, [$this, 'cmpClans']);
  }

  private function sumClans($war, &$clans) {
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

  public function cmpClans($a, $b){
    if ($a['stars'] == $b['stars']) {
      if ($a['destructionPercentage'] == $b['destructionPercentage']) {
        return 0;
      }
      return ($a['destructionPercentage'] < $b['destructionPercentage']) ? 1 : -1;
    }
    return ($a['stars'] < $b['stars']) ? 1 : -1;
  }

}
