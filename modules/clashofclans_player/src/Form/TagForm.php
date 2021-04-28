<?php

namespace Drupal\clashofclans_player\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a ClashOfClans Player form.
 */
class TagForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'clashofclans_player_tag';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['player_tag'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tag of the player'),
      '#size' => 15,
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (mb_substr($form_state->getValue('player_tag'), 0, 1) <> '#') {
      $form_state->setErrorByName('player_tag', $this->t('The first letter should be "#".'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $route = 'clashofclans_player.tag';
    $args = ['tag' => $form_state->getValue('player_tag')];
    $form_state->setRedirect($route, $args);
  }

}
