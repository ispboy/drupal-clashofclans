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

  public function __construct(\Drupal\clashofclans_api\Client $client)
  {
      $this->client = $client;
  }

  public static function create(ContainerInterface $container)
  {
      return new static(
        $container->get('clashofclans_api.client'),
      );
  }

  /**
   * Builds the response.
   */
  public function build() {
    $client = $this->client;

// $test = new \Drupal\clashofclans_api\Test();
// $test->getData('foo');
// $test->getData('bar');
// $test->getData('foo');
// $test->getData('foo');
// $test->getData('bar');
// dpm($test);
    // $query = \Drupal::entityTypeManager()->getStorage('user')->getQuery();
    // $ids = $query->execute();
    // foreach ($ids as $uid) {
    //   $user = \Drupal\user\Entity\User::load($uid);
    //   $old = $user->get('name')->getString();
    //   $new = ltrim($old, '#');
    //   $user->setUsername($new);
    //   $user->save();
    // }

    // $tag = '#Y2QPV0YUP';
    $tag = '#8PUCLC2JP';
    $url = 'players/'. urlencode($tag);
    $data = $client->get($url);
    if (isset($data['legendStatistics']['bestSeason']['id'])) {
      $t = strtotime($data['legendStatistics']['bestSeason']['id']);
      $d = date('Y-m', $t);
    }

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

    $request_time = \Drupal::time()->getCurrentTime();
    // dpm(date('Y-m-d H:i:s', $request_time));


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
    $tag = '#Q09C';
    $url = 'clans/'. urlencode($tag);
    $data = $this->client->get($url);
    // dpm($data['memberList']['#89YLCQ0J9']);

    $build['debug'] = [
      '#theme' => 'clashofclans_api_sample',
      '#data' => $data,
    ];

    if (isset($data['memberList'])) {
      $members = \Drupal\clashofclans_api\Members::getDetail($data['memberList'], $this->client, 10);
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
        // 'legendTrophies' => 'legendTrophies',
        'Best season' => 'bestSeason',
        // 'versusTrophies'  => 'versusTrophies',
        'trophies'  => 'trophies',
      ];
      $build['member_list'] = \Drupal\clashofclans_api\Render::players($members, $fields);
      // $build['#cache']['max-age'] = $this->config('clashofclans_api.settings')->get('cache_max_age');
      // $build['#cache']['max-age'] = 5;
    }
    return $build;
  }

}
