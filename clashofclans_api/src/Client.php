<?php

namespace Drupal\clashofclans_api;

use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Link;
use Drupal\Core\Url;

class Client {

  protected $key;
  protected $httpClient;
  protected $cache_max_age;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->key = \Drupal::config('clashofclans_api.settings')->get('key');
    $base_uri = \Drupal::config('clashofclans_api.settings')->get('base_uri');
    $this->httpClient = \Drupal::service('http_client_factory')->fromOptions([
      'base_uri' => $base_uri,
    ]);;
    $this->cache_max_age = 60;
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
   * @param $items
   * @return array
   */
  public function buildRank($rank, $previousRank) {
    $diff = intval($previousRank) - intval($rank);
    if (intval($rank) <= 0 || intval($previousRank) <=0) {
      $symbol = '';
    } elseif ($diff == 0) {
      $symbol = '(=)';
    } elseif ($diff > 0) {
      $symbol = '(+'. $diff. ')';
    } else {
      $symbol = '('. $diff. ')';
    }
    return $rank. '. '. $symbol;
  }

  /**
   * @param $items
   * @return array
   */
  public function buildPlayers($items, $fields) {
    $rows = [];
    foreach ($items as $key => $item) {
      $row = [];
      foreach ($fields as $field) {
        switch ($field) {
          case 'rank':
            $row[] = $this->buildRank($item['rank'], $item['previousRank']);
            break;
          case 'clanRank':
            $row[] = $this->buildRank($item['clanRank'], $item['previousClanRank']);
            // $row[] = $item['clanRank']. '. '. $item['previousClanRank'];
            break;

          case 'name':
            $row[] = $this->linkPlayer($item['name'], $item['tag']);
            break;

          case 'clan':
            $row[] = isset($item['clan']) ? $this->linkClan($item['clan']['name'], $item['clan']['tag']) : '';
            break;

          case 'league':
            $icon = [
              '#theme' => 'image',
              '#uri' => $item['league']['iconUrls']['tiny'],
              '#width' => 36,
              '#height' => 36,
            ];
            $row[] = \Drupal::service('renderer')->render($icon);
            break;

          default:
            $row[] = isset($item[$field]) ? $item[$field] : '';
        }

      }

      $rows[] = $row;
    }
    $header = array_keys($fields);
    $build = [
      '#type' => 'table',
      '#sticky' => TRUE,
      '#header' => $header,
      '#rows' => $rows,
      '#cache' => [
        'max-age' => $this->cache_max_age,
      ],
    ];

    return $build;
  }

  /**
   * @param $items: data['items']
   * @param $fields: which fields to fetch.
   * @return array
   */
  public function buildClans($items, $fields) {
    $rows = [];
    foreach ($items as $key => $item) {
      $row = [];
      foreach ($fields as $field) {
        switch ($field) {
          case 'rank':
            $row[] = $this->buildRank($item['rank'], $item['previousRank']);
            break;

          case 'name':
            $row[] = $this->linkClan($item['name'], $item['tag']);
            break;

          case 'badge':
            $badge = [
              '#theme' => 'image',
              '#uri' => $item['badgeUrls']['small'],
              '#width' => 64,
              '#height' => 64,
            ];
            $row[] = \Drupal::service('renderer')->render($badge);
            break;

          case 'location':
            if (isset($item['location'])) {
              $row[] = $this->linkLocation($item['location']['name'], $item['location']['id']);
            } else {
              $row[] = '';
            }
            break;

          default:
            $row[] = isset($item[$field]) ? $item[$field] : '';
        }

      }

      $rows[] = $row;
    }

    $header = array_keys($fields);

    $build = [
      '#type' => 'table',
      '#sticky' => TRUE,
      '#header' => $header,
      '#rows' => $rows,
      '#cache' => [
        'max-age' => $this->cache_max_age,
      ],
    ];

    return $build;
  }

  public function getCacheMaxAge() {
    return $this->cache_max_age;
  }

  public function encode($data, $format) {
    switch ($format) {
      case 'json':
        return \Drupal\Component\Serialization\Json::encode($data);
      default:
        return $data;
    }
  }
}
