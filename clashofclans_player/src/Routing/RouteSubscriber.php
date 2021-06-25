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
    $names = [
      'entity.user.canonical',
      'entity.user.edit_form',
      'entity.user.delete_form',
    ];

    foreach ($names as $name) {
      if ($route = $collection->get($name)) {
        $route->setDefault('_title_callback', '\Drupal\clashofclans_player\Controller\PlayerController::userTitle');
      }
    }
  }

}
