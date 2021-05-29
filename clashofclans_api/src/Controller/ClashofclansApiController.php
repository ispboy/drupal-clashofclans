<?php

namespace Drupal\clashofclans_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for ClashOfClans API routes.
 */
class ClashofclansApiController extends ControllerBase {
  private $client;

  public function __construct(\Drupal\clashofclans_api\Client $client)
  {
      $this->client = $client;
  }

  public static function create(ContainerInterface $container)
  {
      $client = $container->get('clashofclans_api.client');
      return new static($client);
  }

  /**
   * Builds the response.
   */
  public function build() {
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('Integrates <a href="@api" target="_blank">Clash of Clans API</a> into Drupal.
        Inspired from <a href="@github" target="_blank">Toniperic</a>.', array(
        '@api' => 'https://developer.clashofclans.com/',
        '@github' => 'https://github.com/toniperic/php-clash-of-clans',
      )),
    ];

    $client = $this->client;

    $tag = '#2VP0J0VV';
    $url = 'clans/' . urlencode($tag);
    $data = $client->getArray($url);
    // dsm($data);
    $build['debug'] = [
      '#theme' => 'clashofclans_api_sample',
      '#data' => $data,
    ];

    $tag = '#2VP0J0VV';
    $url = 'clans/' . urlencode($tag);
    $data = $client->getArray($url);
    dpm($data['name']);
    // foreach ($data['items'] as $key => $item) {
    // }

    return $build;
  }

}
