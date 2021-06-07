<?php
/**
* Helper class
* build internal link
* centialize the path definitations.
**/
namespace Drupal\clashofclans_api;

use Drupal\Core\Link;
use Drupal\Core\Url;

class Link {

  public static function clan($name, $tag) {
    return Link::fromTextAndUrl($name, Url::fromUri('internal:/clashofclans-clan/tag/'. urlencode($tag)))->toString();
  }

  public static function player($name, $tag){
    return Link::fromTextAndUrl($name, Url::fromUri('internal:/clashofclans-player/tag/'. urlencode($tag)))->toString();
  }

  public static function linkLocation($name, $id){
    return Link::fromTextAndUrl($name, Url::fromUri('internal:/clashofclans-location/'. $id))->toString();
  }

}
