<?php

namespace Drupal\clashofclans_api;

use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Link;
use Drupal\Core\Url;

class Client {

  protected $key;
  protected $httpClient;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->key = \Drupal::config('clashofclans_api.settings')->get('key');
    $base_uri = \Drupal::config('clashofclans_api.settings')->get('base_uri');
    $this->httpClient = \Drupal::service('http_client_factory')->fromOptions([
      'base_uri' => $base_uri,
    ]);;
  }

  /**
   * @param $url
   * @return array
   */
  public function get($url){
    $data = &drupal_static(__FUNCTION__);
    $key = urlencode($url);

    if (!isset($data[$key])) {

      $data[$key] = NULL;

        try {
          $options = [
            'headers' => ['authorization' => 'Bearer ' . $this->key],
          ];
          $response = $this->httpClient->request('GET', $url, $options);
          $data[$key] = $response->getBody()->getContents();

        }
        catch (RequestException $error) {
          if ($error->getCode() == 404) {
          } else {
            $logger = \Drupal::logger('ClashOfClans API Request error');
            $logger->error($error->getMessage());
          }
        }

    }
    return $data[$key];

  }

  /**
   * @param $url
   * @return array
   */
  public function getArray($url){
    $content = $this->get($url);
    return $content ? json_decode($content, TRUE) : ['items' => []];
  }

  /**
   * @param $name, $tag
   * @return array
   */
  public function linkClan($name, $tag){
    return Link::fromTextAndUrl($name, Url::fromUri('internal:/clashofclans-clan/tag/'. urlencode($tag)))->toString();
  }

  /**
   * @param $name, $tag
   * @return array
   */
  public function linkPlayer($name, $tag){
    return Link::fromTextAndUrl($name, Url::fromUri('internal:/clashofclans-player/tag/'. urlencode($tag)))->toString();
  }

  /**
   * @param $name, $tag
   * @return array
   */
  public function linkLocation($name, $id){
    return Link::fromTextAndUrl($name, Url::fromUri('internal:/clashofclans-location/'. $id))->toString();
  }

  /**
   * @param $$items
   * @return array
   */
  public function buildPlayers($items) {
    $rows = [];
    foreach ($items as $key => $item) {
      $clan = '';
      if (isset($item['clan'])) {
        $clan = $this->linkClan($item['clan']['name'], $item['clan']['tag']);
      }
      $league = [
        '#theme' => 'image',
        '#uri' => $item['league']['iconUrls']['tiny'],
        '#width' => 36,
        '#height' => 36,
      ];
      $rows[] = [
        $item['rank'],
        $item['previousRank'],
        $this->linkPlayer($item['name'], $item['tag']),
        \Drupal::service('renderer')->render($league),
        $item['expLevel'],
        $item['trophies'],
        $item['attackWins'],
        $item['defenseWins'],
        $clan,
      ];
    }
    $header = ['rank', 'previousRank', 'name', 'league', 'expLevel', 'trophies','attackWins', 'defenseWins', 'clan'];
    $build['content'] = [
      '#type' => 'table',
      '#sticky' => TRUE,
      '#header' => $header,
      '#rows' => $rows,
    ];

    return $build;
  }

  /**
   * @param $$items
   * @return array
   */
  public function buildClans($items, $global = FALSE) {
    $rows = [];
    foreach ($items as $key => $item) {
      $name = $item['name'];
      $tag = $item['tag'];
      $name = $this->linkClan($name, $tag);
      $badge = [
        '#theme' => 'image',
        '#uri' => $item['badgeUrls']['small'],
        '#width' => 64,
        '#height' => 64,
      ];

      $row = [
        $item['rank'],
        $item['previousRank'],
        \Drupal::service('renderer')->render($badge),
        $name,
        $item['clanLevel'],
        $item['members'],
        $item['clanPoints'],
      ];

      if ($global) {
        $location = '';
        if (isset($item['location'])) {
          $location = $this->linkLocation($item['location']['name'], $item['location']['id']);
        }
        $row[] = $location;
      }

      $rows[] = $row;
    }

    $header = ['rank', 'previousRank', 'badge', 'name', 'clanLevel', 'members','clanPoints'];
    if ($global) {
      $header[] = 'location';
    }
    $build['content'] = [
      '#type' => 'table',
      '#sticky' => TRUE,
      '#header' => $header,
      '#rows' => $rows,
    ];

    return $build;
  }
}
