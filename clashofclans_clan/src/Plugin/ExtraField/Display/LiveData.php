<?php

namespace Drupal\clashofclans_clan\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_clan\Clan;

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

  protected $clan;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Clan $clan) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->clan = $clan;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('clashofclans_clan.clan')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    $data = $this->clan->getLiveData($entity);
    if ($data) {
      $data['entity_id'] = $entity->id();
      $build = [
        '#markup' => 'template_preprocess_clashofclans_clan() to unreal the #data.',
        '#data' => $data,
      ];
    } else {
      $build['content'] = ['#markup' => $this->t('No results.')];
    }
    return $build;
  }


}
