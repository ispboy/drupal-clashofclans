<?php

namespace Drupal\clashofclans_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_api\Clan;
use Symfony\Component\HttpFoundation\Response;
use Drupal\clashofclans_api\GuzzleCache;
use Drupal\Core\Link;

/**
 * Returns responses for ClashOfClans API routes.
 */
class ClashofclansApiController extends ControllerBase {
  private $client;
  private $clan;

  public function __construct(GuzzleCache $client, Clan $clan)
  {
      $this->client = $client;
      $this->clan = $clan;
  }

  public static function create(ContainerInterface $container)
  {
      return new static(
        $container->get('clashofclans_api.guzzle_cache'),
        $container->get('clashofclans_api.clan'),
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
    ];

    // $name = '铁血团之彼泽棠棣';
    // $tag = '#22P2GP82P';
    // $url = 'clans/'. urlencode($tag). '/currentwar/leaguegroup';
    // $data = $this->client->get($url);

    // $name = '铁血团之彼泽棠棣';
    // $tag = '#2C9LQQGPP';
    // $url = 'clanwarleagues/wars/'. urlencode($tag);
    // $data = $this->client->get($url);
    // dpm(array_keys($data));

    $data = [];
    $tag = '#2CVPQRL9';
    $url = 'clans/'. urlencode($tag);
    $data = $client->get($url);
    // dpm($data['memberList']['#89YLCQ0J9']);

    $build['debug'] = [
      '#theme' => 'clashofclans_api_sample',
      '#data' => $data,
    ];

    // if (isset($data['memberList'])) {
    //   $members = \Drupal\clashofclans_api\Members::getDetail($data['memberList'], $this->client, 10);
    //   $fields = [
    //     'Rank' => 'clanRank',
    //     'league' => 'league',
    //     'expLevel' => 'expLevel',
    //     'Name'  => 'name',
    //     'role' => 'role',
    //     'donations' => 'donations',
    //     'Received' => 'donationsReceived',
    //     'attackWins' => 'attackWins',
    //     'defenseWins' => 'defenseWins',
    //     // 'legendTrophies' => 'legendTrophies',
    //     'Best season' => 'bestSeason',
    //     // 'versusTrophies'  => 'versusTrophies',
    //     'trophies'  => 'trophies',
    //   ];
    //   $build['member_list'] = \Drupal\clashofclans_api\Render::players($members, $fields);
    //   // $build['#cache']['max-age'] = $this->config('clashofclans_api.settings')->get('cache_max_age');
    //   // $build['#cache']['max-age'] = 5;
    // }
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
