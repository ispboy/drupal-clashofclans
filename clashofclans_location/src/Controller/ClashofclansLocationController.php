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

  public function __construct(\Drupal\clashofclans_api\Client $client) {
      $this->client = $client;
  }

  public static function create(ContainerInterface $container) {
      $client = $container->get('clashofclans_api.client');
      return new static($client);
  }

  /**
   * Builds the Players response.
   */
  public function players(EntityInterface $clashofclans_location) {
    $url = 'locations/'. $clashofclans_location->id(). '/rankings/players';
    $data = $this->client->getArray($url);
    return $this->client->buildPlayers($data['items']);
  }

  public function globalPlayers() {
    $url = 'locations/global/rankings/players';
    $data = $this->client->getArray($url);
    return $this->client->buildPlayers($data['items']);
  }

  public function globalClans() {
    $url = 'locations/global/rankings/clans';
    $data = $this->client->getArray($url);
    return $this->client->buildClans($data['items'], TRUE);
  }

  public function setTitle(EntityInterface $clashofclans_location) {
    return $clashofclans_location->getTitle();
  }

}
