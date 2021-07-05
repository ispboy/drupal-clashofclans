<?php

namespace Drupal\clashofclans_leaguegroup\Plugin\ExtraField\Display;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_api\LeagueGroup;

/**
 * Example Extra field Display.
 *
 * @ExtraFieldDisplay(
 *   id = "clashofclans_leaguegroup_live_data",
 *   label = @Translation("Live data"),
 *   description = @Translation("The real-time data of the League group."),
 *   bundles = {
 *     "leaguegroup.leaguegroup",
 *   }
 * )
 */
class LiveData extends ExtraFieldDisplayBase implements ContainerFactoryPluginInterface {

  protected $leaguegroup;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LeagueGroup $leaguegroup) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->leaguegroup = $leaguegroup;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('clashofclans_api.leaguegroup')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {
    $clans = $entity->get('field_clan')->getValue();
    if ($clans) {
      $clan = current($clans);
      dpm($clan['target_id']);
    }
    // $changed = $entity->get('changed')->getString();
    // dpm(date('Y-m-d H:i:s', $changed));
    // $url = 'players/'. urlencode($tag);
    // $data = $this->client->get($url);
    //
    // if (!isset($data['name'])) {
    //
    //   $build['content'] = [
    //     // '#markup' => t('Game data NOT found!'),
    //   ];
    //
    // } else {
    //
    //   $build['content'] = [
    //     '#theme' => 'clashofclans_player_tag',
    //     '#player' => $data,
    //   ];
    //
    //   if ($this->player->setLegendStatistics($data, $entity)) {
    //     $entity->save();
    //     \Drupal::messenger()->addStatus(t('Player data updated.'));
    //   }
    //   $build['content']['#cache']['max-age'] = $this->client->getCacheMaxAge();
    //
    // }

    return $build;
  }

}
