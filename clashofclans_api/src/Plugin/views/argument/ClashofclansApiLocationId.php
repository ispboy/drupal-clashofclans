<?php
namespace Drupal\clashofclans_api\Plugin\views\argument;

use Drupal\views\Plugin\views\argument\ArgumentPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base argument handler for clashofclans_api_query.
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("clashofclans_api_location_id")
 */
class ClashofclansApiLocationId extends ArgumentPluginBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
    );
  }

  public function query($group_by = FALSE) {
    $this->query->setLocationId($this->argument);
  }

  public function getTitle() {
    $id = $this->argument;
    if ($id == 'global') {
      return $this->t('Global ranking');
    } else {
      $location = $this->entityTypeManager
            ->getStorage('clashofclans_location')
            ->load($id);
      if ($location) {
        return $location->label();
      }
    }
  }
}
