<?php

namespace Drupal\clashofclans_player\Plugin\Field\FieldFormatter;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'clashofclans_player_season_table' formatter.
 *
 * @FieldFormatter(
 *   id = "clashofclans_player_season_table",
 *   label = @Translation("Table"),
 *   field_types = {"clashofclans_player_season"}
 * )
 */
class SeasonTableFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $header[] = '#';
    $header[] = $this->t('ID');
    $header[] = $this->t('Trophies');
    $header[] = $this->t('Rank');

    $table = [
      '#type' => 'table',
      '#header' => $header,
    ];

    foreach ($items as $delta => $item) {
      $row = [];

      $row[]['#markup'] = $delta + 1;

      if ($item->id) {
        $date = DrupalDateTime::createFromFormat('Y-m-d', $item->id);
        $date_formatter = \Drupal::service('date.formatter');
        $timestamp = $date->getTimestamp();
        $formatted_date = $date_formatter->format($timestamp, 'long');
        $iso_date = $date_formatter->format($timestamp, 'custom', 'Y-m-d\TH:i:s') . 'Z';
        $row[] = [
          '#theme' => 'time',
          '#text' => $formatted_date,
          '#html' => FALSE,
          '#attributes' => [
            'datetime' => $iso_date,
          ],
          '#cache' => [
            'contexts' => [
              'timezone',
            ],
          ],
        ];
      }
      else {
        $row[]['#markup'] = '';
      }

      $row[]['#markup'] = $item->trophies;

      $row[]['#markup'] = $item->rank;

      $table[$delta] = $row;
    }

    return [$table];
  }

}
