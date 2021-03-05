<?php

namespace Drupal\clashofclans\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure ClashOfClans settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'clashofclans_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['clashofclans.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['key'] = [
      '#type' => 'textarea',
      '#title' => $this->t('key'),
      '#default_value' => $this->config('clashofclans.settings')->get('key'),
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  // public function validateForm(array &$form, FormStateInterface $form_state) {
  //   if ($form_state->getValue('example') != 'example') {
  //     $form_state->setErrorByName('example', $this->t('The value is not correct.'));
  //   }
  //   parent::validateForm($form, $form_state);
  // }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('clashofclans.settings')
      ->set('key', $form_state->getValue('key'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
