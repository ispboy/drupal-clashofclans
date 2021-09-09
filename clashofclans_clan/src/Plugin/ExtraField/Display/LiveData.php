<?php

namespace Drupal\clashofclans_clan\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_api\Client;
use Drupal\clashofclans_api\Render;

/**
 * Example Extra field Display.
 *
 * @ExtraFieldDisplay(
 *   id = "clashofclans_clan_live_data",
 *   label = @Translation("Live data"),
 *   description = @Translation("The real-time data of the clan."),
 *   bundles = {
 *     "clashofclans_clan.clashofclans_clan",
 *   }
 * )
 */
class LiveData extends ExtraFieldDisplayBase implements ContainerFactoryPluginInterface {

  protected $client;

  /**
   * Constructs a ExtraFieldDisplayFormattedBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Client $client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('clashofclans_api.client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    $tag = $entity->get('clan_tag')->getString();
    // $changed = $entity->get('changed')->getString();
    // dpm(date('Y-m-d H:i:s', $changed));
    $url = 'clans/'. urlencode($tag);
    $data = $this->client->get($url);

    if (!isset($data['name'])) {
      $build['#markup'] = t('No results.');
      return $build;
    }

    $build['content'] = [
      // '#theme' => 'clashofclans_clan_tag',
      // '#markup' => 'Return the #data, #entity_id, #location, #member_list, #cache',
      '#data' => $data,
      '#entity_id' => $entity->id(),
    ];

    if (isset($data['location'])) {
      $location = \Drupal\clashofclans_api\Render::link($data['location']['name'], $data['location']['id'], 'location');
      $build['content']['#location'] = $location;
    }

    // if (isset($data['isWarLogPublic']) && $data['isWarLogPublic']) {
    //   $title = t('Current war');
    //   $build['content']['#current_war'] = Render::link($title, $tag, 'currentwar');
    //
    //   $title = t('League group');
    //   $build['content']['#league_group'] = Render::link($title, $tag, 'leaguegroup');
    // }

    if (isset($data['memberList'])) {
      $fields = [
        'Rank' => 'clanRank',
        'league' => 'league',
        'expLevel' => 'expLevel',
        'Name'  => 'name',
        'role' => 'role',
        'donations' => 'donations',
        'Received' => 'donationsReceived',
        'versusTrophies'  => 'versusTrophies',
        'trophies'  => 'trophies',
      ];
      $table = \Drupal\clashofclans_api\Render::players($data['memberList'], $fields);

      $build['content']['#member_list'] = $table;
    }
    $build['#cache']['max-age'] = $this->client->getCacheMaxAge();

    $this->checkEntity($data, $entity);

    $keys = array_keys($build['content']);
    $build ['#markup'] = 'Check properties: '. implode(',', $keys);
    return $build;
  }

  private function checkEntity($data, ContentEntityInterface $entity) {
    $items = [];

    if (isset($data['description'])) {
      $items['description'] = $data['description'];
    }

    $count = 0; //Count changes
    foreach ($items as $key => $item) {
      $val = $entity->get($key)->getString();
      if (strcmp($val, $item)) {
        $entity->set($key, $item);
        $count ++;
      }
    }
    if ($count) {
      $entity->save();
      \Drupal::messenger()->addStatus(t('Clan data updated.'));
    }

  }

}
