<?php

namespace Drupal\clashofclans_api\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Link;

/**
 * Provides Json field handler.
 *
 * @ViewsField("clashofclans_api_field")
 *
 * @DCG
 * The plugin needs to be assigned to a specific table column through
 * hook_views_data() or hook_views_data_alter().
 * For non-existent columns (i.e. computed fields) you need to override
 * self::query() method.
 */
class ClashOfClansApiField extends FieldPluginBase {

  /**
   * @{inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['key'] = ['default' => ''];
    $options['type'] = ['default' => ''];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Key'),
      '#default_value' => $this->options['key'],
    ];
    $form['type'] = [
      '#type' => 'select',
      '#title' => $this
        ->t('Render type'),
      '#options' => [
        '' => $this->t('None'),
        'Link' => [
          'link_to_player' => $this
            ->t('Link to Player'),
          'link_to_clan' => $this
            ->t('Link to Clan'),
        ],
      ],
      '#default_value' => $this->options['type'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {

    $key = $this->options['key'];
    $type = $this->options['type'];
    if (isset($values->item[$key])) {
      $val = $values->item[$key];
      if ($type == 'link_to_player' && isset($values->item['tag'])) {
        $tag = $values->item['tag'];
        $link = Link::createFromRoute($val, 'clashofclans_player.tag', ['tag' => $tag]);
        return $link->toRenderable();
      }

      if ($type == 'link_to_clan' && isset($values->item['tag'])) {
        $tag = $values->item['tag'];
        $link = Link::createFromRoute($val, 'clashofclans_clan.tag', ['tag' => $tag]);
        return $link->toRenderable();
      }

      return [
        '#theme' => 'clashofclans_api__'. $key,
        '#data' => $val,
      ];
    }
  }

}
