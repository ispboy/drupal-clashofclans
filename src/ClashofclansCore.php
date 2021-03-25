<?php

namespace Drupal\clashofclans;

use ClashOfClans\Client;
use GuzzleHttp\Exception\RequestException;

class ClashofclansCore {

  public static function getClan(string $tag) {
    $clan = &drupal_static(__FUNCTION__);
    if (!isset($clan[$tag])) {
      $key = \Drupal::config('clashofclans.settings')->get('key');
      $client = new Client($key);
      try {
        $clan[$tag] = $client->getClan($tag);
      }
      catch (RequestException $error) {
        if ($error->getCode() <> 404) {
          $logger = \Drupal::logger('ClashOfClans getClan error');
          $logger->error($error->getMessage());
        }
      }
    }
    return $clan[$tag];
  }

  public static function getPlayer(string $tag) {
    $player = &drupal_static(__FUNCTION__);
    if (!isset($player[$tag])) {
      $key = \Drupal::config('clashofclans.settings')->get('key');
      $client = new Client($key);
      try {
        $player[$tag] = $client->getPlayer($tag);
      }
      catch (RequestException $error) {
        if ($error->getCode() <> 404) {
          $logger = \Drupal::logger('ClashOfClans getPlayer error');
          $logger->error($error->getMessage());
        }
      }
    }
    return $player[$tag];
  }

  public static function getRankingsForLocation(string $id = 'global', string $type = 'clan') {
    $rankings = &drupal_static(__FUNCTION__);
    if (!isset($rankings[$id][$type])) {
      $rankings[$id][$type] = [];
      $key = \Drupal::config('clashofclans.settings')->get('key');
      $client = new Client($key);
      try {
        $rankings[$id][$type] = $client->getRankingsForLocation($id, $type);
      }
      catch (RequestException $error) {
        if ($error->getCode() <> 404) {
          $logger = \Drupal::logger('ClashOfClans getClan error');
          $logger->error($error->getMessage());
        }
      }
    }
    return $rankings[$id][$type];
  }
}
