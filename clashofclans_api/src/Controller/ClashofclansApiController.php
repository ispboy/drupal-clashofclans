<?php

namespace Drupal\clashofclans_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\clashofclans_api\GuzzleCache;
use Drupal\Core\Link;

/**
 * Returns responses for ClashOfClans API routes.
 */
class ClashofclansApiController extends ControllerBase {
  private $client;

  public function __construct(GuzzleCache $client)
  {
      $this->client = $client;
  }

  public static function create(ContainerInterface $container)
  {
      return new static(
        $container->get('clashofclans_api.guzzle_cache'),
      );
  }

  /**
   * Builds the response.
   */
  public function build() {
    $client = $this->client;

    $tag = '#Q09C';
    // $name = $this->clan->getName($tag);
    // dpm($name);

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('Integrates <a href="@api" target="_blank">Clash of Clans API</a> into Drupal.
        Inspired from <a href="@github" target="_blank">Toniperic</a>.', array(
        '@api' => 'https://developer.clashofclans.com/',
        '@github' => 'https://github.com/toniperic/php-clash-of-clans',
      )),
    ];

    $build['test'] = [
      '#type' => 'item',
      '#markup' => urldecode('%25'). ' time: '. time(),
    ];

    $items = [];
    $items[] = Link::createFromRoute('Global clans', 'clashofclans_api.location.clans', ['locationId' => 'global']);
    $items[] = Link::createFromRoute('Global players', 'clashofclans_api.location.players', ['locationId' => 'global']);
    $build['links'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#title' => 'Useful links',
      '#items' => $items,
      '#attributes' => [
        'class' => ['links','inline'],
      ],
    ];

    return $build;
  }

  public function passThrough() {
    $route_name = \Drupal::routeMatch()->getRouteName();
    $items = explode('.', $route_name);

    $tpl = implode('__', $items);

    $root = \Drupal::config('clashofclans_api.settings')->get('api_root');
    $url = \Drupal::request()->getRequestUri();
    $url = str_replace($root. '/', '', $url);
    // $data = $this->client->get($url, [], 'json');
    $data = $this->client->get($url);
    if ($data) {
      // $response = new Response();
      // $response->setContent($data);
      // $response->headers->set('Content-Type', 'application/json');
      // $response->setPublic();
      // $response->setMaxAge(60);
      // return $response;
      $build['content'] = [
        '#theme' => $tpl,
        // '#theme' => 'clashofclans_api',
        '#data' => $data,
      ];
    } else {
      $build['content'] = [
        '#markup' => $this->t('No results.'),
      ];
    }
    return $build;
  }

}
