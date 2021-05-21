<?php

namespace Drupal\clashofclans;

use ClashOfClans\Client;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Link;
use Drupal\Core\Url;

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

  protected function getClansForLocation($args = ['id' => 'global']) {
    $rows = [];
    $rankings = $this->client->getRankingsForLocation($args['id'], 'clans');
    foreach($rankings as $key => $ranking) {
      $badge = [
        '#theme' => 'image',
        '#uri' => $ranking->badgeUrls()->small(),
        '#width' => 64,
        '#height' => 64,
      ];
      $tag = $ranking->tag();
      $name = Link::fromTextAndUrl($ranking->name(), Url::fromRoute('clashofclans_clan.tag', ['tag' => $tag]))->toString();

      $items = [
        $ranking->rank(),
        $ranking->previousRank(),
        \Drupal::service('renderer')->render($badge),
        $name,
        $ranking->clanLevel(),
        $ranking->members(),
        $ranking->clanPoints(),
      ];
      if ($args['id'] == 'global') {
        if ($ranking->location()) {
          $items[] = Link::fromTextAndUrl($ranking->location()->name(), Url::fromRoute('entity.clashofclans_location.canonical', ['clashofclans_location' => $ranking->location()->id()]))->toString();
        } else {
          $items[] = '';
        }
      }
      $rows[] = $items;

    }

    $header = ['rank', 'previousRank', 'badge', 'name', 'clanLevel', 'members','clanPoints'];
    if ($args['id'] == 'global') {
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

  protected function getPlayersForLocation($args = ['id' => 'global']) {
    $rows = [];

    $rankings = $this->client->getRankingsForLocation($args['id'], 'players');
      // $first = current($rankings);
      // ksm($first->clan()->name());
    foreach($rankings as $key => $ranking) {
      $tag = $ranking->tag();
      $name = Link::fromTextAndUrl($ranking->name(), Url::fromRoute('clashofclans_player.tag', ['tag' => $tag]))->toString();
      $clan = '';
      if ($ranking->clan()) {
        $clan = Link::fromTextAndUrl($ranking->clan()->name(), Url::fromRoute('clashofclans_clan.tag', ['tag' => $ranking->clan()->tag()]))->toString();
        // $clan = $ranking->clan()->name();
      }

      $league = [
        '#theme' => 'image',
        '#uri' => $ranking->league()->iconUrls()->small(),
        '#width' => 36,
        '#height' => 36,
      ];

      $rows[] = [
        $ranking->rank(),
        $ranking->previousRank(),
        $name,
        \Drupal::service('renderer')->render($league),
        $ranking->expLevel(),
        $ranking->trophies(),
        $ranking->attackWins(),
        $ranking->defenseWins(),
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

  protected function getClan($args) {
    return $this->client->getClan($args['tag']);
  }

  protected function getPlayer($args) {
    return $this->client->getPlayer($args['tag']);
  }

  protected function verifyPlayer($args) {
    $client = $this->client;
    $tag = $args['tag'];
    $token = $args['token'];  //This is not the api token, but player owner token.
    $url = 'players/' . urlencode($tag). '/verifytoken';
    $options = [
      'headers' => ['authorization' => 'Bearer ' . $client->getToken()],
      'body' => json_encode(['token' => $token]),
    ];

    $response = $client->getHttpClient()
      ->request('POST', $url, $options);

    $body = json_decode($response->getBody()->getContents(), true);
    return ($body['status'] == 'ok') ? TRUE : FALSE;
  }

}
