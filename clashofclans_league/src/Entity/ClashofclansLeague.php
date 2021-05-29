<?php

namespace Drupal\clashofclans_league\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\clashofclans_league\ClashofclansLeagueInterface;
use Drupal\user\UserInterface;

/**
 * Defines the league entity class.
 *
 * @ContentEntityType(
 *   id = "clashofclans_league",
 *   label = @Translation("League"),
 *   label_collection = @Translation("Leagues"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\clashofclans_league\ClashofclansLeagueListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\clashofclans_league\ClashofclansLeagueAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\clashofclans_league\Form\ClashofclansLeagueForm",
 *       "edit" = "Drupal\clashofclans_league\Form\ClashofclansLeagueForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "clashofclans_league",
 *   data_table = "clashofclans_league_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer league",
 *   entity_keys = {
 *     "id" = "id",
 *     "langcode" = "langcode",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/content/clashofclans-league/add",
 *     "canonical" = "/clashofclans-league/{clashofclans_league}",
 *     "edit-form" = "/admin/content/clashofclans-league/{clashofclans_league}/edit",
 *     "delete-form" = "/admin/content/clashofclans-league/{clashofclans_league}/delete",
 *     "collection" = "/admin/content/clashofclans-league"
 *   },
 *   field_ui_base_route = "entity.clashofclans_league.settings"
 * )
 */
class ClashofclansLeague extends ContentEntityBase implements ClashofclansLeagueInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   *
   * When a new league entity is created, set the uid entity reference to
   * the current user as the creator of the entity.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += ['uid' => \Drupal::currentUser()->id()];
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled() {
    return (bool) $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus($status) {
    $this->set('status', $status);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setTranslatable(TRUE)
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the league entity.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('A boolean indicating whether the league is enabled.'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setTranslatable(TRUE)
      ->setLabel(t('Author'))
      ->setDescription(t('The user ID of the league author.'))
      ->setSetting('target_type', 'user')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the league was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the league was last edited.'));

    return $fields;
  }

}
