<?php

namespace Drupal\clashofclans_clan\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'clashofclans_clan' field type.
 *
 * @FieldType(
 *   id = "clashofclans_clan",
 *   label = @Translation("Clan"),
 *   category = @Translation("General"),
 *   default_widget = "clashofclans_clan",
 *   default_formatter = "clashofclans_clan_default"
 * )
 */
class ClanItem extends FieldItemBase {

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
    $properties['description'] = DataDefinition::create('string')
      ->setLabel(t('Description'));

    return $properties;
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
      'description' => [
        'type' => 'varchar',
        'length' => 255,
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

    $values['description'] = $random->word(mt_rand(1, 255));

    return $values;
  }

}
