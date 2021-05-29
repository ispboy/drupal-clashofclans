<?php

namespace Drupal\clashofclans_clan\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Returns responses for ClashOfClans Clan routes.
 */
class ClanController extends ControllerBase {

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

  public function setTitle($tag) {
    $title = $tag;  //provide default title, if not found.
    $url = 'clans/'. urlencode($tag);
    $data = $this->client->getArray($url);

    if (isset($data['name'])) {
      $title = $data['name'];
    }
    return $title;
  }

  /**
   * Builds the response.
   */
  public function tag($tag) {
    $url = 'clans/'. urlencode($tag);
    $data = $this->client->getArray($url);

    if (!isset($data['name'])) {
      $build['content'] = [
        '#markup' => $this->t('Not found!'),
      ];

      return $build;
    }

    $build['clan'] = [
      '#theme' => 'clashofclans_clan_tag',
      '#clan' => $data,
    ];

    if (isset($data['location'])) {
      $location = $this->client->linkLocation($data['location']['name'], $data['location']['id']);
      $build['clan']['#location'] = $location;
    }

    if (isset($data['memberList'])) {
      $rows = [];
      foreach ($data['memberList'] as $key => $item) {
        $league = [
          '#theme' => 'image',
          '#uri' => $item['league']['iconUrls']['tiny'],
          '#width' => 36,
          '#height' => 36,
        ];
        $rows[] = [
          $item['clanRank'],
          $item['previousClanRank'],
          $this->client->linkPlayer($item['name'], $item['tag']),
          $item['role'],
          $item['expLevel'],
          $item['donations'],
          $item['donationsReceived'],
          $item['versusTrophies'],
          $item['trophies'],
          \Drupal::service('renderer')->render($league),
        ];
      }
      $header = ['Rank', 'previous', 'name', 'role', 'expLevel', 'donations', 'Received', 'versusTrophies', 'trophies', 'league'];
      $table['content'] = [
        '#type' => 'table',
        '#sticky' => TRUE,
        '#header' => $header,
        '#rows' => $rows,
      ];
    }

    $build['clan']['#member_list'] = $table;

    return $build;

  }

}
