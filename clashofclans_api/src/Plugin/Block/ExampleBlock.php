<?php

namespace Drupal\clashofclans_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_api\Clan;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "clashofclans_api_example",
 *   admin_label = @Translation("Example"),
 *   category = @Translation("ClashOfClans")
 * )
 */
class ExampleBlock extends BlockBase implements ContainerFactoryPluginInterface {

  private $clan;

  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Session\AccountInterface $account
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Clan $clan) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->clan = $clan;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('clashofclans_api.clan')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [
      '#markup' => $this->t('It works!'),
    ];
    return $build;
  }

}
