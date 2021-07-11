<?php

namespace Drupal\clashofclans_player\Plugin\Field\FieldFormatter;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'clashofclans_player_season_default' formatter.
 *
 * @FieldFormatter(
 *   id = "clashofclans_player_season_default",
 *   label = @Translation("Default"),
 *   field_types = {"clashofclans_player_season"}
 * )
 */
class SeasonDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {

      if ($item->id) {
        $date = DrupalDateTime::createFromFormat('Y-m-d', $item->id);
        // @DCG: Consider injecting the date formatter service.
        // @codingStandardsIgnoreStart
        $date_formatter = \Drupal::service('date.formatter');
        // @codingStandardsIgnoreStart
        $timestamp = $date->getTimestamp();
        $formatted_date = $date_formatter->format($timestamp, 'html_month');
        $iso_date = $date_formatter->format($timestamp, 'custom', 'Y-m-d\TH:i:s') . 'Z';

        if ($item->rank) {
          $element[$delta]['rank'] = [
            // '#type' => 'item',
            '#title' => $this->t('Rank'),
            '#prefix' => '<div>🧍‍♂️',
            '#markup' => $item->rank,
            '#postfix' => '</div>',
          ];
        }

        $element[$delta]['id'] = [
          // '#type' => 'item',
          '#title' => $this->t('ID'),
          'content' => [
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
            '#prefix' => '<div>🗓',
            '#postfix' => '</div>',
          ],
        ];
      }

      if ($item->trophies) {
        $element[$delta]['trophies'] = [
          // '#type' => 'item',
          '#title' => $this->t('Trophies'),
          '#prefix' => '<div>🏆',
          '#markup' => $item->trophies,
          '#postfix' => '</div>',
        ];
      }

    }

    return $element;
  }

}
