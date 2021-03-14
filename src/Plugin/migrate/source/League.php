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

    $key = \Drupal::config('clashofclans.settings')->get('key');
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
