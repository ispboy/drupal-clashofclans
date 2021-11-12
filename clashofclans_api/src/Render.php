<?php
namespace Drupal\clashofclans_api;

class Render {
  public static function table($header = [], $items = [], $context = '') {
    $rows = [];
    foreach ($items as $item) {
      $row = [];
      foreach (array_keys($header) as $key) {
        if ($key == 'name' && isset($item['tag']) && $context) {
          if ($context == 'player') {
            $link = \Drupal\Core\Link::createFromRoute($item[$key], 'clashofclans_player.tag', ['tag' => $item['tag']]);
          } elseif ($context == 'clan') {
            $link = \Drupal\Core\Link::createFromRoute($item[$key], 'clashofclans_clan.tag', ['tag' => $item['tag']]);
          }
          $renderable = $link->toRenderable();
        } else {
          $renderable = [
            '#theme' => 'clashofclans_api__'. $key,
            '#data' => $item[$key],
          ];
        }
        $row[] = \Drupal::service('renderer')->renderPlain($renderable);
      }
      $rows[] = $row;
    }

    $renderable = [
      '#type' => 'table',
      // '#responsive' => FALSE,
      '#sticky' => TRUE,
      '#header' => $header,
      '#rows' => $rows,
    ];

    return $renderable;
  }
}
