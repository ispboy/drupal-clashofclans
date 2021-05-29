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
    parent::submitForm($form, $form_state);
  }

}
