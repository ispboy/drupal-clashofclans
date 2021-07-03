<?php

namespace Drupal\clashofclans_player\Plugin\Field\FieldFormatter;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'clashofclans_player_season_key_value' formatter.
 *
 * @FieldFormatter(
 *   id = "clashofclans_player_season_key_value",
 *   label = @Translation("Key-value"),
 *   field_types = {"clashofclans_player_season"}
 * )
 */
class SeasonKeyValueFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $element = [];

    foreach ($items as $delta => $item) {

      $table = [
        '#type' => 'table',
      ];

      // ID.
      if ($item->id) {
        $date = DrupalDateTime::createFromFormat('Y-m-d', $item->id);
        $date_formatter = \Drupal::service('date.formatter');
        $timestamp = $date->getTimestamp();
        $formatted_date = $date_formatter->format($timestamp, 'long');
        $iso_date = $date_formatter->format($timestamp, 'custom', 'Y-m-d\TH:i:s') . 'Z';

        $table['#rows'][] = [
          'data' => [
            [
              'header' => TRUE,
              'data' => [
                '#markup' => $this->t('ID'),
              ],
            ],
            [
              'data' => [
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
              ],
            ],
          ],
          'no_striping' => TRUE,
        ];
      }

      // Trophies.
      if ($item->trophies) {
        $table['#rows'][] = [
          'data' => [
            [
              'header' => TRUE,
              'data' => [
                '#markup' => $this->t('Trophies'),
              ],
            ],
            [
              'data' => [
                '#markup' => $item->trophies,
              ],
            ],
          ],
          'no_striping' => TRUE,
        ];
      }

      // Rank.
      if ($item->rank) {
        $table['#rows'][] = [
          'data' => [
            [
              'header' => TRUE,
              'data' => [
                '#markup' => $this->t('Rank'),
              ],
            ],
            [
              'data' => [
                '#markup' => $item->rank,
              ],
            ],
          ],
          'no_striping' => TRUE,
        ];
      }

      $element[$delta] = $table;

    }

    return $element;
  }

}
