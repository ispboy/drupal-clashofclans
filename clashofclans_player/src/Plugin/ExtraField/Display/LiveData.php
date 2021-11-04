<?php

namespace Drupal\clashofclans_player\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_player\Player;
use Drupal\Core\StringTranslation\StringTranslationTrait;

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

  use StringTranslationTrait;

  protected $player;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Player $player) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->player = $player;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('clashofclans_player.player')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    $data = $this->player->getLiveData($entity);
    if ($data) {
      $data['entity_id'] = $entity->id();
      $build = [
        '#markup' => 'clashofclans_player_preprocess_user() to unreal the #data.',
        '#data' => $data,
      ];
    } else {
      $build = ['#markup' => $this->t('No results.')];
    }
    return $build;
  }


}
