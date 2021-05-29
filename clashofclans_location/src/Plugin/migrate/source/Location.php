<?php

namespace Drupal\clashofclans_location\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Row;
use Drupal\clashofclans_api\Client;


/**
 * The 'clashofclans_location' source plugin.
 *
 * @MigrateSource(
 *   id = "clashofclans_location",
 *   source_module = "clashofclans_location"
 * )
 */
class Location extends SourcePluginBase {

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    // @DCG You may return something meaningful here.
    return 'ClashOfClans Location source';
  }

  /**
   * {@inheritdoc}
   */
  protected function initializeIterator() {

    // @DCG
    // In this example we return a hardcoded set of records.
    //
    // For large sets of data consider using generators like follows:
    // @code
    // foreach ($foo->nextRecord() as $record) {
    //  yield $record;
    // }
    // @endcode

    $client = new Client();
    $url = 'locations';
    $data = $client->getArray($url);

    $records = [];
    foreach ($data['items'] as $key => $location) {
      $countryCode = NULL;
      if ($location['isCountry']) {
        $countryCode = $location['countryCode'];
      }
      $records[] = [
        'id' => $location['id'],
        'name' => $location['name'],
        'isCountry' => $location['isCountry'],
        'countryCode' => $countryCode,
      ];
    }

    return new \ArrayIterator($records);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'id' => $this->t('The location ID.'),
      'name' => $this->t('The location name.'),
      'countryCode' => $this->t('The location countryCode.'),
      'isCountry' => $this->t('The location isCountry.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'id' => [
        'type' => 'integer',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {

    // @DCG
    // Extend/modify the row here if needed.
    //
    // Example:
    // @code
    // $name = $row->getSourceProperty('name');
    // $row->setSourceProperty('name', Html::escape('$name');
    // @endcode
    return parent::prepareRow($row);
  }

}
