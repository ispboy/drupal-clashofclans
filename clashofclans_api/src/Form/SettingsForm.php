<?php

namespace Drupal\clashofclans_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure ClashOfClans API settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'clashofclans_api_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['clashofclans_api.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['base_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base URI'),
      '#description' => $this->t('e.g. https://api.clashofclans.com/v1/'),
      '#default_value' => $this->config('clashofclans_api.settings')->get('base_uri'),
      '#required' => TRUE,
    ];
    $form['key'] = [
      '#type' => 'textarea',
      '#title' => $this->t('key'),
      '#default_value' => $this->config('clashofclans_api.settings')->get('key'),
      '#required' => TRUE,
    ];
    $form['player'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Super player'),
      '#description' => $this->t('The tag of a player who has all the troops, spells & heros, etc.'),
      '#default_value' => $this->config('clashofclans_api.settings')->get('player'),
    ];
    $form['cache_max_age'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cache: max-age'),
      '#description' => $this->t('The general cache max-age'),
      '#default_value' => $this->config('clashofclans_api.settings')->get('cache_max_age'),
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('clashofclans_api.settings')
      ->set('base_uri', $form_state->getValue('base_uri'))
      ->save();
    $this->config('clashofclans_api.settings')
      ->set('key', $form_state->getValue('key'))
      ->save();
    $this->config('clashofclans_api.settings')
      ->set('player', $form_state->getValue('player'))
      ->save();
      $this->config('clashofclans_api.settings')
        ->set('cache_max_age', $form_state->getValue('cache_max_age'))
        ->save();
    parent::submitForm($form, $form_state);
  }

}
