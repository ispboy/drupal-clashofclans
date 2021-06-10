<?php

namespace Drupal\clashofclans_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_api\Link;
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
      '#markup' => urldecode('%25'). ' time: '. time(),
    ];

    $client = $this->client;

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
    $tag = '#LJVLYGLV';
    $data = $this->clan->get($tag, 8);
    // dpm($data['memberList']['#89YLCQ0J9']);

    $build['debug'] = [
      '#theme' => 'clashofclans_api_sample',
      '#data' => $data,
    ];

    if (isset($data['memberList'])) {
      $fields = [
        'Rank' => 'clanRank',
        'league' => 'league',
        'expLevel' => 'expLevel',
        'Name'  => 'name',
        'role' => 'role',
        'donations' => 'donations',
        'Received' => 'donationsReceived',
        'attackWins' => 'attackWins',
        'defenseWins' => 'defenseWins',
        'legendTrophies' => 'legendTrophies',
        'Best season' => 'bestSeason',
        'versusTrophies'  => 'versusTrophies',
        'trophies'  => 'trophies',
      ];
      $build['member_list'] = \Drupal\clashofclans_api\Render::players($data['memberList'], $fields);
      // $build['#cache']['max-age'] = $this->config('clashofclans_api.settings')->get('cache_max_age');
      // $build['#cache']['max-age'] = 5;
    }
    return $build;
  }

}
