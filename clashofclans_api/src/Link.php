<?php
/**
* Helper class
* build internal link
* centialize the path definitations.
**/
namespace Drupal\clashofclans_api;

use Drupal\Core\Link as DrupalLink;
use Drupal\Core\Url;

class Link {

  public static function clan($name, $tag) {
    return DrupalLink::fromTextAndUrl($name, Url::fromRoute('clashofclans_clan.tag', ['tag' => $tag]));
  }

  public static function player($name, $tag){
    return DrupalLink::fromTextAndUrl($name, Url::fromRoute('clashofclans_player.tag', ['tag' => $tag]));
  }

  // public static function war($name, $tag){
  //   return DrupalLink::fromTextAndUrl($name, Url::fromRoute('clashofclans_clan.clanwarleagues.war', ['tag' => $tag]));
  // }

  public static function location($name, $id){
    return DrupalLink::fromTextAndUrl($name, Url::fromUri('internal:/clashofclans-location/'. $id));
  }

}
