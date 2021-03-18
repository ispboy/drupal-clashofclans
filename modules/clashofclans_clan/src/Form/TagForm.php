<?php

namespace Drupal\clashofclans_clan\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a ClashOfClans Clan form.
 */
class TagForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'clashofclans_clan_tag';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['clan_tag'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tag of the clan'),
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
    if (mb_substr($form_state->getValue('clan_tag'), 0, 1) <> '#') {
      $form_state->setErrorByName('clan_tag', $this->t('The first letter should be "#".'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $route = 'clashofclans_clan.tag';
    $args = ['tag' => $form_state->getValue('clan_tag')];
    $form_state->setRedirect($route, $args);
  }

}
