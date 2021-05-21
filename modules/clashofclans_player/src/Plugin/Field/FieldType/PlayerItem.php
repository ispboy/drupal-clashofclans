<?php

namespace Drupal\clashofclans_player\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'clashofclans_player' field type.
 *
 * @FieldType(
 *   id = "clashofclans_player",
 *   label = @Translation("Player"),
 *   category = @Translation("General"),
 *   default_widget = "clashofclans_player",
 *   default_formatter = "clashofclans_player_default"
 * )
 */
class PlayerItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    if ($this->name !== NULL) {
      return FALSE;
    }
    elseif ($this->tag !== NULL) {
      return FALSE;
    }
    elseif ($this->token !== NULL) {
      return FALSE;
    }
    elseif ($this->verified !== NULL) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    $properties['name'] = DataDefinition::create('string')
      ->setLabel(t('Name'));
    $properties['tag'] = DataDefinition::create('string')
      ->setLabel(t('Tag'));
    $properties['token'] = DataDefinition::create('string')
      ->setLabel(t('Token'));
    $properties['verified'] = DataDefinition::create('integer')
      ->setLabel(t('Verified'));

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
      'name' => [
        'type' => 'varchar',
        'length' => 255,
      ],
      'tag' => [
        'type' => 'varchar',
        'length' => 255,
      ],
      'token' => [
        'type' => 'varchar',
        'length' => 255,
      ],
      'verified' => [
        'type' => 'int',
        'size' => 'tiny',
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

    $random = new Random();

    $values['name'] = $random->word(mt_rand(1, 255));

    $values['tag'] = $random->word(mt_rand(1, 255));

    $values['token'] = $random->word(mt_rand(1, 255));

    return $values;
  }

}
