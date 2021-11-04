<?php

namespace Drupal\clashofclans_player\Plugin\Field\FieldFormatter;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

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
  public static function defaultSettings() {
    return ['keys' => ['id' => 'id']] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();
    $options = [
      'id' => $this->t('ID'),
      'rank' => $this->t('Rank'),
      'trophies' => $this->t('Trophies'),
    ];
    $element['keys'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Show'),
      '#options' => $options,
      '#default_value' => $settings['keys'],
      '#required' => TRUE,
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->getSettings();
    $keys = array_filter($settings['keys']);
    $summary[] = $this->t('Show: @keys', ['@keys' => implode(', ', $keys)]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    $settings = $this->getSettings();
    $keys = array_filter($settings['keys']);

    foreach ($items as $delta => $item) {

      if ($item->id) {
        $date = DrupalDateTime::createFromFormat('Y-m-d', $item->id);
        // @DCG: Consider injecting the date formatter service.
        // @codingStandardsIgnoreStart
        $date_formatter = \Drupal::service('date.formatter');
        // @codingStandardsIgnoreStart
        $timestamp = $date->getTimestamp();
        $formatted_date = $date_formatter->format($timestamp, 'custom', 'Y-m');
        $iso_date = $date_formatter->format($timestamp, 'custom', 'Y-m-d\TH:i:s') . 'Z';
        $element[$delta]['id'] = [
          '#type' => 'item',
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
          ],
        ];
      }

      if ($item->rank) {
        $element[$delta]['rank'] = [
          '#type' => 'item',
          '#title' => $this->t('Rank'),
          '#markup' => $item->rank,
          '#title_display' => 'attribute',
        ];
      }

      if ($item->trophies) {
        $element[$delta]['trophies'] = [
          '#type' => 'item',
          '#title' => $this->t('Trophies'),
          '#markup' => $item->trophies,
        ];
      }

      $element[$delta] = array_intersect_key($element[$delta], $keys);

    }

    return $element;
  }

}
