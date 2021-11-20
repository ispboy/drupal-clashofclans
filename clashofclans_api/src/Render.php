<?php
namespace Drupal\clashofclans_api;

class Render {
  public static function table($header = [], $items = [], $sticky = TRUE, $rows = []) {
    if (!$rows) {
      foreach ($items as $item) {
        $row = [];
        foreach (array_keys($header) as $key) {
          switch ($key) {
            case 'rank':
            case 'clanRank':
              $rank = intval($item[$key]);
              $previous = intval($item['previous'. ucfirst($key)]);
              $diff = NULL;
              if ($previous) {
                $diff = $previous - $rank;
                if ($diff > 999) {
                  $diff = 999;
                }
              }
              $renderable = [
                '#theme' => 'clashofclans_api__rank',
                '#data' => ['rank' => $rank, 'diff' => $diff],
              ];
              break;
            case 'name':
              if (isset($item['expLevel'])) {
                $link = \Drupal\Core\Link::createFromRoute($item[$key], 'clashofclans_player.tag', ['tag' => $item['tag']]);
              } elseif (isset($item['clanLevel'])) {
                $link = \Drupal\Core\Link::createFromRoute($item[$key], 'clashofclans_clan.tag', ['tag' => $item['tag']]);
              }
              $renderable = $link->toRenderable();
              break;
            default:
              if (isset($item[$key])) {
                $renderable = [
                  '#theme' => 'clashofclans_api__'. $key,
                  '#data' => $item[$key],
                ];
              } else {
                $renderable = NULL;
              }
          }

          $row[] = \Drupal::service('renderer')->renderPlain($renderable);
        }
        $rows[] = $row;
      }
    }

    $renderable = [
      '#type' => 'table',
      // '#responsive' => FALSE,
      '#sticky' => $sticky,
      '#header' => $header,
      '#rows' => $rows,
    ];

    return $renderable;
  }
}
