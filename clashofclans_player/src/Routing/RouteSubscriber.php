<?php

namespace Drupal\clashofclans_player\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // dpm(($collection));
    if ($route = $collection->get('entity.user.canonical')) {
      $route->setDefault('_title_callback', '\Drupal\clashofclans_player\Controller\PlayerController::userTitle');
    }
  }

}
