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
        '#markup' => $this->t('Not found!'),
      ];

    } else {

      $build['content'] = [
        '#theme' => 'clashofclans_player_tag',
        '#player' => $data,
      ];
      if (isset($data['clan'])) {
        $clan = \Drupal\clashofclans_api\Render::link($data['clan']['name'], $data['clan']['tag'], 'clan');
        $build['content']['#clan'] = $clan;
      }

      $build['content']['#cache']['max-age'] = $this->client->getCacheMaxAge();

    }

    return $build;
  }

}
