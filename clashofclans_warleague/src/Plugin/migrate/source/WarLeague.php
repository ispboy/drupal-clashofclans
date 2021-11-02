<?php

namespace Drupal\clashofclans_warleague\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Row;

/**
 * The 'clashofclans_warleague' source plugin.
 *
 * @MigrateSource(
 *   id = "clashofclans_warleague",
 *   source_module = "clashofclans_warleague"
 * )
 */
class WarLeague extends SourcePluginBase {

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    // @DCG You may return something meaningful here.
    return 'ClashOfClans WarLeague source';
  }

  /**
   * {@inheritdoc}
   */
  protected function initializeIterator() {

    $client = \Drupal::service('clashofclans_api.guzzle_cache');
    $url = 'warleagues';
    $data = $client->getData($url);

    $records = [];
    foreach ($data['items'] as $key => $item) {
      $records[] = [
        'id' => $item['id'],
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
      'id' => $this->t('The WarLeague ID.'),
      'name' => $this->t('The WarLeague name.'),
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
