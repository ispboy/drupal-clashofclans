<?php

namespace Drupal\clashofclans_player\Plugin\ExtraField\Display;

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
 *   id = "clashofclans_player_live_data",
 *   label = @Translation("Live data"),
 *   description = @Translation("The real-time data of the player."),
 *   bundles = {
 *     "user.user",
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
    $tag = $entity->get('field_player_tag')->getString();
    // $changed = $entity->get('changed')->getString();
    // dpm(date('Y-m-d H:i:s', $changed));
    $url = 'players/'. urlencode($tag);
    $data = $this->client->get($url);

    if (!isset($data['name'])) {

      $build['content'] = [
        // '#markup' => t('Game data NOT found!'),
      ];

    } else {

      $build['content'] = [
        '#theme' => 'clashofclans_player_tag',
        '#player' => $data,
      ];

      $this->checkEntity($data, $entity);
      $build['content']['#cache']['max-age'] = $this->client->getCacheMaxAge();

    }

    return $build;
  }

  private function checkEntity($data, ContentEntityInterface $entity) {
    $items = [];

    if (isset($data['legendStatistics']['bestSeason']['id'])) {
      $timestamp = strtotime($data['legendStatistics']['bestSeason']['id']);
      $date = date('Y-m-d', $timestamp);
      $items['field_best_season'] = $date;
    }

    if (isset($data['legendStatistics']['legendTrophies'])) {
      $items['field_legend_trophies'] = $data['legendStatistics']['legendTrophies'];
    }

    if (isset($data['bestTrophies'])) {
      $items['field_best_trophies'] = $data['bestTrophies'];
    }

    $count = 0; //Count changes
    foreach ($items as $key => $item) {
      $val = $entity->get($key)->getString();
      if (strcmp($val, $item)) {
        $entity->set($key, $item);
        if ($key == 'field_best_season') {
          $entity->set('field_best_season_rank', $data['legendStatistics']['bestSeason']['rank']);
          $entity->set('field_best_season_trophies', $data['legendStatistics']['bestSeason']['trophies']);
        }
        $count ++;
      }
    }
    if ($count) {
      $entity->save();
      \Drupal::messenger()->addStatus(t('Player data updated.'));
    }

  }

}
