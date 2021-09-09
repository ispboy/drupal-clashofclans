<?php
namespace Drupal\clashofclans_api\Routing;

use Symfony\Component\Routing\Route;

/**
 * Defines dynamic routes.
 */
class ClashofclansApiRoutes {

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $root = \Drupal::config('clashofclans_api.settings')->get('api_root');
    $locations = [
      'clashofclans_api.location.clans' => '/locations/{locationId}/rankings/clans',
      'clashofclans_api.location.players' => '/locations/{locationId}/rankings/players',
      'clashofclans_api.location.clans_versus' => '/locations/{locationId}/rankings/clans-versus',
      'clashofclans_api.location.players_versus' => '/locations/{locationId}/rankings/players-versus',
    ];
    $routes = [];

    foreach ($locations as $key => $val) {
      $routes[$key] = new Route(
        // Path to attach this route to:
        $root . $val,
        // Route defaults:
        [
          '_controller' => '\Drupal\clashofclans_api\Controller\ClashofclansApiController::passThrough',
        ],
        // Route requirements:
        [
          '_permission'  => 'access content',
        ]
      );
    }

    return $routes;
  }

}
