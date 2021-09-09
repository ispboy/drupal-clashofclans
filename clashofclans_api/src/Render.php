<?php
/**
* Helper class
* build renderable array
**/
namespace Drupal\clashofclans_api;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;

class Render {

  public static function image($url, $width='36', $height='36') {
    $build = [
      '#theme' => 'image',
      '#uri' => $url,
      '#width' => $width,
      '#height' => $height,
    ];
    return \Drupal::service('renderer')->render($build);
  }

  /**
  * @param $rank, $previousRank
  * @return string
  **/
  public static function rank($rank, $previousRank) {
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
   * @return array for table
   */
  public static function players($items, $fields) {
    $rows = [];
    foreach ($items as $key => $item) {
      $row = [];
      foreach ($fields as $field) {
        switch ($field) {
          case 'rank':
            $row[] = self::rank($item['rank'], $item['previousRank']);
            break;
          case 'clanRank':
            $row[] = self::rank($item['clanRank'], $item['previousClanRank']);
            break;

          case 'name':
            $row[] = self::link($item['name'], $item['tag'], 'player');
            break;

          case 'clan':
            $row[] = isset($item['clan']) ? self::link($item['clan']['name'], $item['clan']['tag'], 'clan') : '';
            break;

          case 'league':
            $row[] = self::image($item['league']['iconUrls']['tiny'], 36, 36);
            break;

          case 'legendTrophies':
            $row[] = isset($item['legendStatistics']['legendTrophies'])? $item['legendStatistics']['legendTrophies']: '';
            break;

          case 'bestSeason':
            $data = isset($item['legendStatistics']['bestSeason']) ?
              Markup::create(
                // $item['legendStatistics']['bestSeason']['id']. '<br />🏆'.
                // $item['legendStatistics']['bestSeason']['trophies']. '<br />📌'.
                '📌'. $item['legendStatistics']['bestSeason']['rank']
              ) : '';
            $row[] = [
              'data' => $data,
              'nowrap' => 'nowrap',
            ];
            break;

            case 'previousSeason':
              $data = isset($item['legendStatistics']['previousSeason']) ?
                Markup::create(
                  // $item['legendStatistics']['previousSeason']['id']. '<br />🏆'.
                  // $item['legendStatistics']['previousSeason']['trophies']. '<br />📌'.
                  '📌'. $item['legendStatistics']['previousSeason']['rank']
                ) : '';
              $row[] = [
                'data' => $data,
                'nowrap' => 'nowrap',
              ];
              break;

          default:
            $row[] = isset($item[$field]) ? $item[$field] : '';
        }

      }

      $rows[] = $row;
    }

    $header = [];
    foreach ($fields as $key => $field) {
      $th = [];
      $highs = ['rank', 'clanRank', 'name', 'clanPoints', 'trophies', 'attackWins'];
      $th['data'] = $key;
      if (!in_array($field, $highs)) {
        $th['class'] = [RESPONSIVE_PRIORITY_MEDIUM];
      }
      $header[] = $th;
    }
    $build = [
      '#type' => 'table',
      '#attributes' => ['class' => ['clashofclans-players-table']],
      '#sticky' => FALSE,
      '#responsive' => FALSE,
      '#header' => $header,
      '#rows' => $rows,
      '#cache' => ['max-age' => \Drupal::config('clashofclans_api.settings')->get('cache_max_age')],
    ];

    return $build;
  }

  /**
   * @param $items: data['items']
   * @param $fields: which fields to fetch.
   * @return array
   */
  public static function clans($items, $fields) {
    $rows = [];
    foreach ($items as $key => $item) {
      $row = [];
      foreach ($fields as $field) {
        switch ($field) {
          case 'rank':
            $row[] = self::rank($item['rank'], $item['previousRank']);
            break;

          case 'name':
            $row[] = self::link($item['name'], $item['tag'], 'clan');
            break;

          case 'badge':
            $row[] = self::image($item['badgeUrls']['small'], 35, 35);
            break;

          case 'clanPoints':
            $row[] = isset($item[$field]) ? $item[$field] : '';
            break;

          case 'location':
            if (isset($item['location'])) {
              $row[] = self::link($item['location']['name'], $item['location']['id'], 'location');
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

    $header = [];
    foreach ($fields as $key => $field) {
      $th = [];
      $highs = ['rank', 'clanRank', 'name', 'clanPoints', 'trophies'];
      $th['data'] = $key;
      if (!in_array($field, $highs)) {
        $th['class'] = [RESPONSIVE_PRIORITY_MEDIUM];
      }
      $header[] = $th;
    }

    $build = [
      '#type' => 'table',
      '#sticky' => FALSE,
      '#responsive' => FALSE,
      '#header' => $header,
      '#rows' => $rows,
      '#cache' => ['max-age' => \Drupal::config('clashofclans_api.settings')->get('cache_max_age')],
    ];

    return $build;
  }

  /**
   * purpose: because the Drupal Link object would convert the '#' to fragment, or '%23' to '%2523'!
   * so build it myself.
   * @param $items: data['items']
   * @param $fields: which fields to fetch.
   * @return array
   */
  public static function link($name, $tag, $type) {

    $urls = [ //define the path centrally.
      'clan' => Url::fromUri('internal:/clashofclans-clan/tag/')->toString(). urlencode($tag),
      'player' => Url::fromUri('internal:/clashofclans-player/tag/')->toString(). urlencode($tag),
      // 'warlog' => Url::fromUri('internal:/clashofclans-clan/tag/')->toString(). urlencode($tag). '/warlog',
      // 'currentwar' => Url::fromUri('internal:/clashofclans-clan/tag/')->toString(). urlencode($tag). '/currentwar',
      // 'leaguegroup' => Url::fromUri('internal:/clashofclans-clan/tag/')->toString(). urlencode($tag). '/leaguegroup',
      'location' => Url::fromUri('internal:/clashofclans-location/'. $tag),
    ];

    $build = [
      '#theme' => 'clashofclans_api_link',
      '#url' => $urls[$type],
      '#title' => $name,
    ];
    return \Drupal::service('renderer')->render($build);
  }
}
