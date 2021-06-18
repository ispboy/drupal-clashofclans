<?php
/**
* MemberList class: fetch more data from player-source,  merge to the memberList.
* Used by memberlist.
**/
namespace Drupal\clashofclans_api;
use Drupal\clashofclans_api\Client;

class Members {

  public static function getDetail($members=[], Client $client, $limit = 5) {
    $items = [];
    $count = 0;
    foreach ($members as $player) {
      $tag = $player['tag'];
      $items[$tag] = $player;
      if ($count < $limit) {
        $url = 'players/'. urlencode($tag);
        $detail = $client->get($url);
        $items[$tag]['attackWins'] = $detail['attackWins'];
        $items[$tag]['defenseWins'] = $detail['defenseWins'];
        if (isset($detail['legendStatistics'])) {
          $items[$tag]['legendStatistics'] = $detail['legendStatistics'];
        }
      }
      $count++;
    }
    return $items;
  }

}
