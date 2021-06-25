<?php

namespace Drupal\clashofclans_api;

class Test {
  private $count=0;
  private $url;

  public function getData($url) {
    $data =& drupal_static(__FUNCTION__);
    if (!isset($data[$url])) {
      $data[$url] = $url;
      $this->url = $url;
      $this->count ++;
      dpm($url);
    }
    return $data;
  }

}
