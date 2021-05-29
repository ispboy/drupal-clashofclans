<?php

namespace Drupal\clashofclans_clan\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Row;
use Drupal\clashofclans_api\Client;


/**
 * The 'clashofclans_location_clan' source plugin.
 *
 * @MigrateSource(
 *   id = "clashofclans_clan",
 *   source_module = "clashofclans_clan"
 * )
 */
class Clan extends SourcePluginBase {

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    // @DCG You may return something meaningful here.
    return 'ClashOfClans Global Clan source';
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
    $url = 'locations/global/rankings/clans';
    $data = $client->getArray($url);

    $records = [];
    foreach ($data['items'] as $key => $item) {
      $records[] = [
        'tag' => $item['tag'],
        'name' => $item['name'],
      ];
    }

    return new \ArrayIterator($records);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'tag' => $this->t('The clan tag.'),
      'name' => $this->t('The clan name.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'tag' => [
        'type' => 'string',
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
