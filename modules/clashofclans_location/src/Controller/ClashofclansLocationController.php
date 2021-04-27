<?php

namespace Drupal\clashofclans_location\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Returns responses for Clashofclans location routes.
 */
class ClashofclansLocationController extends ControllerBase {
  private $client;

  public function __construct(\Drupal\clashofclans\ClashofclansClient $client)
  {
      $this->client = $client;
  }

  public static function create(ContainerInterface $container)
  {
      $client = $container->get('clashofclans.client');
      return new static($client);
  }

  /**
   * Builds the Players response.
   */
  public function players(EntityInterface $clashofclans_location) {
    return $this->client->get('getPlayersForLocation', ['id' => $clashofclans_location->id()]);
  }

  public function globalClans() {
    return $this->client->get('getClansForLocation', ['id' => 'global']);
  }

  public function globalPlayers() {
    return $this->client->get('getPlayersForLocation', ['id' => 'global']);
  }

  public function setTitle(EntityInterface $clashofclans_location) {
    return $clashofclans_location->getTitle();
  }

}
