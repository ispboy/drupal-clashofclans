<?php

namespace Drupal\clashofclans_api\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Link;

/**
 * Provides Json field handler.
 *
 * @ViewsField("clashofclans_api_data")
 *
 * @DCG
 * The plugin needs to be assigned to a specific table column through
 * hook_views_data() or hook_views_data_alter().
 * For non-existent columns (i.e. computed fields) you need to override
 * self::query() method.
 */
class ClashOfClansApiData extends FieldPluginBase {
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
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $str_key = $this->options['key'];
    $entity = $values->_entity;
    if ($entity->field_data->value) {
      $json = $entity->field_data->value;
      $data = \Drupal\Component\Serialization\Json::decode($json);
      $keys = explode('/', $str_key);
      $val = $data;
      foreach ($keys as $key) {
        if (isset($val[$key])) {
          $val = $val[$key];
        } else {
          $val = NULL;
        }
      }

      if ($val) {
        return [
          '#theme' => 'clashofclans_api__'. $key,
          '#data' => $val,
        ];
      }
    }
  }

}
