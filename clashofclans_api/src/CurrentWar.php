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
    if (isset($data['clan']['members'])) {
      $this->fetchMembers($data['clan']['members'], $this->players);
      $this->fetchMembers($data['opponent']['members'], $this->players);
      uasort($this->data['clan']['members'], [$this, 'cmpMapPosition']);
      uasort($this->data['opponent']['members'], [$this, 'cmpMapPosition']);
    }

  }

  public function getData() {
    return $this->data;
  }

  public function getPlayers() {
    return $this->players;
  }

  public function fetchMembers($members, &$players) {
    foreach ($members as $member) {
      $tag = $member['tag'];
      $name = $member['name'];
      $players[$tag] = $name;
    }
  }

  public function cmpMapPosition($a, $b){
    if ($a['mapPosition'] == $b['mapPosition']) {
      return 0;
    }
    return ($a['mapPosition'] > $b['mapPosition']) ? 1 : -1;
  }
}
