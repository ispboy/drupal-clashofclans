<?php

namespace Drupal\clashofclans_player\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'Rank Only' formatter.
 *
 * @FieldFormatter(
 *   id = "clashofclans_player_season_rank",
 *   label = @Translation("Rank Only"),
 *   field_types = {"clashofclans_player_season"}
 * )
 */
class SeasonRankFormatter extends FormatterBase {
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      if ($item->rank) {
        $element[$delta]['rank'] = [
          // '#type' => 'item',
          '#title' => $this->t('Rank'),
          '#prefix' => '<div>📌',
          '#markup' => $item->rank,
          '#postfix' => '</div>',
        ];
      }
    }

    return $element;
  }

}
