<?php

namespace Drupal\clashofclans_player\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Defines the 'clashofclans_player_season' field widget.
 *
 * @FieldWidget(
 *   id = "clashofclans_player_season",
 *   label = @Translation("Season"),
 *   field_types = {"clashofclans_player_season"},
 * )
 */
class SeasonWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['id'] = [
      '#type' => 'datetime',
      '#title' => $this->t('ID'),
      '#default_value' => NULL,
      '#date_time_element' => 'none',
      '#date_time_format' => '',
      '#date_year_range' => '2006:2099',
    ];
    if (isset($items[$delta]->id)) {
      $element['id']['#default_value'] = DrupalDateTime::createFromFormat(
        'Y-m-d',
        $items[$delta]->id,
        'UTC'
      );
    }

    $element['trophies'] = [
      '#type' => 'number',
      '#title' => $this->t('Trophies'),
      '#default_value' => isset($items[$delta]->trophies) ? $items[$delta]->trophies : NULL,
    ];

    $element['rank'] = [
      '#type' => 'number',
      '#title' => $this->t('Rank'),
      '#default_value' => isset($items[$delta]->rank) ? $items[$delta]->rank : NULL,
    ];

    $element['#theme_wrappers'] = ['container', 'form_element'];
    $element['#attributes']['class'][] = 'clashofclans-player-season-elements';
    $element['#attached']['library'][] = 'clashofclans_player/clashofclans_player_season';

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    return isset($violation->arrayPropertyPath[0]) ? $element[$violation->arrayPropertyPath[0]] : $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $delta => $value) {
      if ($value['id'] === '') {
        $values[$delta]['id'] = NULL;
      }
      if ($value['id'] instanceof DrupalDateTime) {
        $values[$delta]['id'] = $value['id']->format('Y-m-d');
      }
      if ($value['trophies'] === '') {
        $values[$delta]['trophies'] = NULL;
      }
      if ($value['rank'] === '') {
        $values[$delta]['rank'] = NULL;
      }
    }
    return $values;
  }

}
