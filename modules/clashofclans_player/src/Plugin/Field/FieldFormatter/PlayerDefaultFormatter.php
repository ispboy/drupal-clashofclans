<?php

namespace Drupal\clashofclans_player\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'clashofclans_player_default' formatter.
 *
 * @FieldFormatter(
 *   id = "clashofclans_player_default",
 *   label = @Translation("Default"),
 *   field_types = {"clashofclans_player"}
 * )
 */
class PlayerDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {

      if ($item->name && $item->tag) {

        $element[$delta]['name'] = [
          '#type' => 'link',
          '#title' => $item->name,
          '#url' => \Drupal\Core\Url::fromRoute('clashofclans_player.tag', ['tag' => $item->tag]),
        ];

        if ($item->verified) {
          $element[$delta]['verified'] = [
            '#markup' => $this->t('Verified'),
          ];
        }

      } else {
        if ($item->name) {
          $element[$delta]['name'] = [
            '#type' => 'item',
            '#title' => $this->t('Name'),
            '#markup' => $item->name,
          ];

        }

        if ($item->tag) {
          $element[$delta]['tag'] = [
            '#type' => 'item',
            '#title' => $this->t('Tag'),
            '#markup' => $item->tag,
          ];
        }

      }
    }

    return $element;
  }

}
