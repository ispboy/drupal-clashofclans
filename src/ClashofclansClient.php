<?php

namespace Drupal\clashofclans;

use ClashOfClans\Client;
use GuzzleHttp\Exception\RequestException;

class ClashofclansClient {
  /**
   * @var Client $client
   */
  protected $client;

  /**
   * Class constructor.
   */
  public function __construct() {
    $key = \Drupal::config('clashofclans.settings')->get('key');
    $this->client = new Client($key);
  }

  public function get($action = 'getRankingsForLocation', $args = []) {
    $data = &drupal_static(__FUNCTION__);
    if (!isset($data[$action][$args])) {
      $cid = implode('.', [
        'clashofclans',
        $action,
        implode('.', $args),
      ]);
      $key = $cid;  //build the $data[$key];
      $cid .= ':'. \Drupal::languageManager()->getCurrentLanguage()->getId();

      $data[$key] = [];

      if ($cache = \Drupal::cache()->get($cid)) {
        $data[$key] = $cache->data;
      } else {
        try {
          $data[$key] = $this->$action($args);
          \Drupal::cache()->set($cid, $data[$key], time()+180);
        }
        catch (RequestException $error) {
          if ($error->getCode() == 404) {
            \Drupal::cache()->set($cid, $data[$key], time()+3600*24*100);
          } else {
            $logger = \Drupal::logger('ClashOfClans getClan error');
            $logger->error($error->getMessage());
          }
        }
      }
    }
    return $data[$key];
  }

  protected function getRankingsForLocation($args = ['id' => 'global', 'type' => 'clans']) {
    return $this->client->getRankingsForLocation($args['id'], $args['type']);
  }

  protected function getClan($args) {
    return $this->client->getClan($args['tag']);
  }

  protected function getPlayer($args) {
    return $this->client->getPlayer($args['tag']);
  }

}
