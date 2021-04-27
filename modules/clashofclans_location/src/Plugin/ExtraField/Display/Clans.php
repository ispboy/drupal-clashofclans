<?php

namespace Drupal\clashofclans_location\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans\ClashofclansClient;

/**
 * Example Extra field Display.
 *
 * @ExtraFieldDisplay(
 *   id = "clashofclans_location_clans",
 *   label = @Translation("Clans"),
 *   description = @Translation("The rankings of clan in the location."),
 *   bundles = {
 *     "clashofclans_location.clashofclans_location",
 *   }
 * )
 */
class Clans extends ExtraFieldDisplayBase implements ContainerFactoryPluginInterface {

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ClashofclansClient $client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('clashofclans.client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    return $this->client->get('getClansForLocation', ['id' => $entity->id()]);
  }

}
