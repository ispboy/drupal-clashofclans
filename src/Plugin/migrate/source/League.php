<?php

namespace Drupal\clashofclans\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Row;
use ClashOfClans\Client;


/**
 * The 'clashofclans_league' source plugin.
 *
 * @MigrateSource(
 *   id = "clashofclans_league",
 *   source_module = "clashofclans"
 * )
 */
class League extends SourcePluginBase {

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    // @DCG You may return something meaningful here.
    return 'ClashOfClans League source';
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

    $key = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiIsImtpZCI6IjI4YTMxOGY3LTAwMDAtYTFlYi03ZmExLTJjNzQzM2M2Y2NhNSJ9.eyJpc3MiOiJzdXBlcmNlbGwiLCJhdWQiOiJzdXBlcmNlbGw6Z2FtZWFwaSIsImp0aSI6ImVlNTI2MTgzLTNjNzMtNDM5Ny04MjEzLTQ0MzVkMGZjNzJjOCIsImlhdCI6MTYxNDQxNzIxMywic3ViIjoiZGV2ZWxvcGVyLzA1NzEyNTQ0LWE5ZDktMWRmMy1lNzRhLWZjZDIwYWJmZjA1ZCIsInNjb3BlcyI6WyJjbGFzaCJdLCJsaW1pdHMiOlt7InRpZXIiOiJkZXZlbG9wZXIvc2lsdmVyIiwidHlwZSI6InRocm90dGxpbmcifSx7ImNpZHJzIjpbIjE2Ny43MS4xNTEuMTcyIl0sInR5cGUiOiJjbGllbnQifV19.W6UhL0IAkUTbBk41v5LNj2EHE5r_Ozgx68TU-5DJuvfKVAij0KoFADsZ81TGVUKQcenMemZZRwuIxMoQZ42UYw';
    $client = new Client($key);

    // $clan = $client->getClan('#C00RJP'); // returns Clan object
    // $clan->name(); // "Hattrickers"
    // $clan->level(); // 8
    // $clan->warWins(); // 168
    // $leader = $clan->memberList()->coleaders();
    // $player = $client->getPlayer('#P9RJUCR2U');
    // ksm($player->clan());

    $leagues = $client->getLeagues();
    $records = [];
    foreach ($leagues as $key => $league) {
      $records[] = [
        'id' => $league->id(),
        'name' => $league->name(),
        'icon' => $league->icon()->small(),
      ];
    }

    return new \ArrayIterator($records);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'id' => $this->t('The league ID.'),
      'name' => $this->t('The league name.'),
      'icon' => $this->t('The league icon.'),
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
