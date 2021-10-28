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
    if (!$root) {
      $root = '/api';
    }
    $routes = [];

    $routes['clashofclans_api'] = new Route(
      $root,
      // Route defaults:
      [
        '_controller' => '\Drupal\clashofclans_api\Controller\ClashofclansApiController::cutThrough',
      ],
      // Route requirements:
      [
        '_permission'  => 'access content',
        '_csrf_token' => 'TRUE',
      ]
    );

    return $routes;
  }

}
