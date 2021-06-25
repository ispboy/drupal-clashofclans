<?php
/**
*  Processing Data from 'GET /clans/{clanTag}/currentwar'
**/
namespace Drupal\clashofclans_api;

class CurrentWar {

  private $data;
  private $players = []; //[ 'tag' => 'name', ... ]
  /**
   * Class constructor.
   */
  public function __construct($data) {
    $this->data = $data;
    $this->data['events'] = [];
    $this->processData($data);
  }

  public function getData() {
    return $this->data;
  }

  public function getPlayers() {
    return $this->players;
  }

  /**
  * processData
  **/
  public function processData($data) {
    //fetch players from clan
    if (isset($data['clan']['members'])) {
      $this->parseMembers('clan');
    }
    //fetch players from opponent
    if (isset($data['opponent']['members'])) {
      $this->parseMembers('opponent');
    }

    //stat from opponent.
    if (isset($data['opponent']['members'])) {
      $this->parseOpponent($this->data['clan'], $this->data['opponent']);
    }

    //stat from opponent.
    if (isset($data['clan']['members'])) {
      $this->parseOpponent($this->data['opponent'], $this->data['clan']);
    }

    //sort war events
    if (isset($this->data['events'])) {
      krsort($this->data['events']);
    }



  }

  /**
  * calc war statistics.
  **/
  public function parseOpponent(&$clan, &$opponent) {
    $clan['bestAttacks'] = [0, 0, 0, 0];  // index. star
    foreach ($opponent['members'] as $member) {
      if (isset($member['bestOpponentAttack'])) {
        $stars = $member['bestOpponentAttack']['stars'];
        $tag = $member['bestOpponentAttack']['attackerTag'];
        $i = intval($stars);
        $clan['bestAttacks'][$i] ++;
        $clan['bestPlayers'][$tag]['stars'][$i] ++;
      }
    }
    uasort($clan['bestPlayers'], [$this, 'cmpPlayerStars']);
// dpm($clan['bestPlayers']);
  }

  /**
  * fetch all members to players
  **/
  public function parseMembers($type = 'clan') {
    $clan =& $this->data[$type];
    $clan['bestPlayers'] = [];
    foreach ($clan['members'] as $member) {

      $tag = $member['tag'];
      $this->players[$tag] = $member;

      //initial bestPlayers' stars
      $clan['bestPlayers'][$tag] = [
        'name' => $member['name'],
        'stars' => [0, 0, 0, 0],
      ];

      //log the war events
      if (isset($member['attacks'])) {
        foreach ($member['attacks'] as $event) {
          $order = $event['order'];
          $this->data['events'][$order] = $event;
          $this->data['events'][$order]['type'] = $type;
          if ($type == 'clan') {
            $this->data['events'][$order]['leftTag'] = $event['attackerTag'];
            $this->data['events'][$order]['rightTag'] = $event['defenderTag'];
          } elseif ($type == 'opponent') {
            $this->data['events'][$order]['leftTag'] = $event['defenderTag'];
            $this->data['events'][$order]['rightTag'] = $event['attackerTag'];
          }
        }
      }

    }
    uasort($clan['members'], [$this, 'cmpMapPosition']);
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
