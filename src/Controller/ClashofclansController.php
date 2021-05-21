<?php

namespace Drupal\clashofclans\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Returns responses for ClashOfClans routes.
 */
class ClashofclansController extends ControllerBase {
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
   * Builds the response.
   */
  public function build() {
    $client = $this->client;
    $tag = '#P9RJUCR2U';
    $token = 'm7n3t7zs';
    $verify = $client->get('verifyPlayer', ['tag' => $tag, 'token' => $token]);
    dpm($verify);


    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
