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
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('<a href="@project" target="_blank">drupal-clashofclans</a> Integrates <a href="@api" target="_blank">Clash of Clans API</a> into Drupal.
        Inspired from <a href="@github" target="_blank">Toniperic</a>.', array(
        '@project' => 'https://github.com/ispboy/drupal-clashofclans/tree/3.0.x',
        '@api' => 'https://developer.clashofclans.com/',
        '@github' => 'https://github.com/toniperic/php-clash-of-clans',
      )),
    ];

    $build['test'] = [
      '#type' => 'item',
      '#markup' => urldecode('%25'). ' time: '. time(),
    ];

    $items = [];
    $options = [
      'attributes' => ['target' => '_blank'],
      'query' => [
        'url' => 'locations/global/rankings/clans',
        'limit' => 10,
        'token' => $client->getCsrfToken(),
      ],
    ];
    $items[] = Link::createFromRoute('Global clans', 'clashofclans_api', [], $options);

    $options = [
      'attributes' => ['target' => '_blank'],
      'query' => [
        'url' => 'locations/global/rankings/players',
        'limit' => 10,
        'token' => $client->getCsrfToken(),
      ],
    ];
    $items[] = Link::createFromRoute('Global players', 'clashofclans_api', [], $options);

    $options = [
      'attributes' => ['target' => '_blank'],
      'query' => [
        'url' => 'leagues/29000022/seasons/2021-09',
        'limit' => 10,
        'after' => 'eyJwb3MiOjEwfQ',
        'token' => $client->getCsrfToken(),
      ],
    ];
    $items[] = Link::createFromRoute('leagues/29000022/seasons/2021-09', 'clashofclans_api', [], $options);

    $options = [
      'attributes' => ['target' => '_blank'],
      'query' => [
        'url' => 'players/#P9RJUCR2U',
        'limit' => 10,
        'token' => $client->getCsrfToken(),
      ],
    ];
    $items[] = Link::createFromRoute('蓝竹-画雅人', 'clashofclans_api', [], $options);

    $options = [
      'attributes' => ['target' => '_blank'],
      'query' => [
        'url' => 'clans/#9YR8JU00',
        'limit' => 10,
        'token' => $client->getCsrfToken(),
      ],
    ];
    $items[] = Link::createFromRoute('快乐糖果屋', 'clashofclans_api', [], $options);

    $options = [
      'attributes' => ['target' => '_blank'],
      'query' => [
        'url' => 'clans/#C00RJP/warlog',
        'limit' => 5,
        'after' => 'eyJwb3MiOjUwfQ',
        'token' => $client->getCsrfToken(),
      ],
    ];
    $items[] = Link::createFromRoute('War log', 'clashofclans_api', [], $options);

    $options = [
      'attributes' => ['target' => '_blank'],
      'query' => [
        'url' => 'clans/#PCUJJ2GQ/currentwar/leaguegroup',
        'token' => $client->getCsrfToken(),
      ],
    ];
    $items[] = Link::createFromRoute('联赛', 'clashofclans_api', [], $options);

    $build['links'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#title' => 'Useful links',
      '#items' => $items,
      '#attributes' => [
        'class' => ['links','inline'],
      ],
    ];

    $url = 'locations/global/rankings/clans';
    $data = $client->getData($url, ['query' => ['limit' => 60]]);
    if (isset($data['items'])) {
      $header = [];
      $rows = [];
      foreach ($data['items'] as $item) {
        if (!$header) {
          $header = array_keys($item);
          array_unshift($header, array_pop($header));
          array_unshift($header, array_pop($header));
        }
        $row = [];
        foreach ($header as $key) {
          $renderable = [
            '#theme' => 'clashofclans_api__'. $key,
            '#data' => $item[$key],
          ];
          $row[] = \Drupal::service('renderer')->renderPlain($renderable);
        }
        $rows[] = $row;
      }
      $build['table'] = [
        '#type' => 'table',
        '#sticky' => TRUE,
        // '#responsive' => FALSE,
        '#header' => $header,
        '#rows' => $rows,
      ];
    }



    return $build;
  }

  public function cutThrough() {
    $client = $this->client;
    $data = NULL;
    $query =  \Drupal::request()->query->all();

    if (isset($query['url'])) {
        $url = $query['url'];
        unset($query['url']);

        $options = [];
        if ($query) {
          $options['query'] = $query;
        }

        $data = $client->getJson($url, $options);
    }

    if ($data) {
      $response = new Response();
      $response->setContent($data);
      $response->headers->set('Content-Type', 'application/json');
      $response->setPublic();
      $response->setMaxAge($client->getMaxAge());
      return $response;
    } else {
      $build['content'] = [
        '#markup' => $this->t('No results.'),
      ];
      return $build;
    }
  }

}
