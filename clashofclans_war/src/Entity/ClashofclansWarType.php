<?php

namespace Drupal\clashofclans_war\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the War type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "clashofclans_war_type",
 *   label = @Translation("War type"),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\clashofclans_war\Form\ClashofclansWarTypeForm",
 *       "edit" = "Drupal\clashofclans_war\Form\ClashofclansWarTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "list_builder" = "Drupal\clashofclans_war\ClashofclansWarTypeListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   admin_permission = "administer war types",
 *   bundle_of = "clashofclans_war",
 *   config_prefix = "clashofclans_war_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/clashofclans_war_types/add",
 *     "edit-form" = "/admin/structure/clashofclans_war_types/manage/{clashofclans_war_type}",
 *     "delete-form" = "/admin/structure/clashofclans_war_types/manage/{clashofclans_war_type}/delete",
 *     "collection" = "/admin/structure/clashofclans_war_types"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *   }
 * )
 */
class ClashofclansWarType extends ConfigEntityBundleBase {

  /**
   * The machine name of this war type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the war type.
   *
   * @var string
   */
  protected $label;

}
