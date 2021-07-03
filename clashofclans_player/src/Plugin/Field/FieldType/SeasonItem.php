<?php

namespace Drupal\clashofclans_player\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'clashofclans_player_season' field type.
 *
 * @FieldType(
 *   id = "clashofclans_player_season",
 *   label = @Translation("Season"),
 *   category = @Translation("General"),
 *   default_widget = "clashofclans_player_season",
 *   default_formatter = "clashofclans_player_season_default"
 * )
 */
class SeasonItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    if ($this->id !== NULL) {
      return FALSE;
    }
    elseif ($this->trophies !== NULL) {
      return FALSE;
    }
    elseif ($this->rank !== NULL) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    $properties['id'] = DataDefinition::create('datetime_iso8601')
      ->setLabel(t('ID'));
    $properties['trophies'] = DataDefinition::create('integer')
      ->setLabel(t('Trophies'));
    $properties['rank'] = DataDefinition::create('integer')
      ->setLabel(t('Rank'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();

    // @todo Add more constraints here.
    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {

    $columns = [
      'id' => [
        'type' => 'varchar',
        'length' => 20,
      ],
      'trophies' => [
        'type' => 'int',
        'size' => 'normal',
      ],
      'rank' => [
        'type' => 'int',
        'size' => 'normal',
      ],
    ];

    $schema = [
      'columns' => $columns,
      // @DCG Add indexes here if necessary.
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {

    $timestamp = \Drupal::time()->getRequestTime() - mt_rand(0, 86400 * 365);
    $values['id'] = gmdate('Y-m-d', $timestamp);

    $values['trophies'] = mt_rand(-1000, 1000);

    $values['rank'] = mt_rand(-1000, 1000);

    return $values;
  }

}
