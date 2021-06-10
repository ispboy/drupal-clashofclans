<?php
/**
* Helper class
* build renderable array
**/
namespace Drupal\clashofclans_api;

use Drupal\clashofclans_api\Link;
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
            $row[] = Link::player($item['name'], $item['tag']);
            break;

          case 'clan':
            $row[] = isset($item['clan']) ? Link::clan($item['clan']['name'], $item['clan']['tag']) : '';
            break;

          case 'league':
            $row[] = self::image($item['league']['iconUrls']['tiny'], 36, 36);
            break;

          case 'legendTrophies':
            $row[] = isset($item['legendStatistics']['legendTrophies'])? $item['legendStatistics']['legendTrophies']: '';
            break;

          case 'bestSeason':
            $row[] = isset($item['legendStatistics']['bestSeason']) ?
              Markup::create(
                $item['legendStatistics']['bestSeason']['id']. '<br />Trophies: '.
                $item['legendStatistics']['bestSeason']['trophies']. '<br />Rank: '.
                $item['legendStatistics']['bestSeason']['rank']
              ) : '';
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
            $row[] = Link::clan($item['name'], $item['tag']);
            break;

          case 'badge':
            $row[] = self::image($item['badgeUrls']['small'], 64, 64);
            break;

          case 'location':
            if (isset($item['location'])) {
              $row[] = Link::location($item['location']['name'], $item['location']['id']);
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
      '#cache' => ['max-age' => \Drupal::config('clashofclans_api.settings')->get('cache_max_age')],
    ];

    return $build;
  }

}
