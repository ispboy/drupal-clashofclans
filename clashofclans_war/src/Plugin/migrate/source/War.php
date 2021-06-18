<?php

namespace Drupal\clashofclans_war\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Row;
use Drupal\clashofclans_api\Client;


/**
 * The 'clashofclans_war' source plugin.
 *
 * @MigrateSource(
 *   id = "clashofclans_war",
 *   source_module = "clashofclans_war"
 * )
 */
class War extends SourcePluginBase {

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    // @DCG You may return something meaningful here.
    return 'ClashOfClans a certain wars source';
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
    $name = '铁血团之彼泽棠棣';
    $tag = '#22P2GP82P';
    $url = 'clans/'. urlencode($tag). '/currentwar/leaguegroup';
    $data = $client->get($url);
    $records = [];
    if (isset($data['rounds'])) {
      foreach ($data['rounds'] as $round) {
        foreach ($round['warTags'] as $war_tag) {
          if ($war_tag != '#0') {
            $url = 'clanwarleagues/wars/'. urlencode($war_tag);
            $war = $client->get($url, 'json');
            $records[] = [
              'tag' => $war_tag,
              'data' => $war,
            ];
          }
        }
      }
    }

    return new \ArrayIterator($records);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'tag' => $this->t('The war tag.'),
      'data' => $this->t('The war data.'),
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
