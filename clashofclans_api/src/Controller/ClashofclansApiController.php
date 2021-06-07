<?php

namespace Drupal\clashofclans_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for ClashOfClans API routes.
 */
class ClashofclansApiController extends ControllerBase {
  private $client;
  private $clan;

  public function __construct(\Drupal\clashofclans_api\Client $client, \Drupal\clashofclans_api\Clan $clan)
  {
      $this->client = $client;
      $this->clan = $clan;
  }

  public static function create(ContainerInterface $container)
  {
      return new static(
        $container->get('clashofclans_api.client'),
        $container->get('clashofclans_api.clan')
      );
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

    $build['test'] = [
      '#markup' => 'time: '. time(),
      '#cache' => [
        'keys' => ['test'],
        'max-age' => 5,
      ],
    ];

    $client = $this->client;

    $tag = '#C00RJP';
    // $url = 'clans/' . urlencode($tag);
    $data = $this->clan->get($tag, 8);
    dpm($data['memberList']['#CV80RVP2']);
    $build['debug'] = [
      '#theme' => 'clashofclans_api_sample',
      '#data' => $data,
    ];


    return $build;
  }

}
