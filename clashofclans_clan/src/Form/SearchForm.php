<?php

namespace Drupal\clashofclans_clan\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a ClashOfClans Clan form.
 */
class SearchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'clashofclans_clan_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#size' => 15,
      '#default_value' => \Drupal::request()->query->get('name'),
    ];

    $form['warFrequency'] = [
      '#type' => 'textfield',
      '#title' => $this->t('warFrequency'),
      '#size' => 15,
      '#default_value' => \Drupal::request()->query->get('warFrequency'),
    ];

    $form['minMembers'] = [
      '#type' => 'textfield',
      '#title' => $this->t('minMembers'),
      '#size' => 15,
      '#default_value' => \Drupal::request()->query->get('minMembers'),
    ];

    $form['minClanPoints'] = [
      '#type' => 'textfield',
      '#title' => $this->t('minClanPoints'),
      '#size' => 15,
      '#default_value' => \Drupal::request()->query->get('minClanPoints'),
    ];

    $form['minClanLevel'] = [
      '#type' => 'textfield',
      '#title' => $this->t('minClanLevel'),
      '#size' => 15,
      '#default_value' => \Drupal::request()->query->get('minClanLevel'),
    ];

    $form['limit'] = [
      '#type' => 'hidden',
      '#value' => 50,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#name' => '',
    ];

    // $form['#method'] = 'GET';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // if (mb_substr($form_state->getValue('clan_tag'), 0, 1) <> '#') {
    //   $form_state->setErrorByName('clan_tag', $this->t('The first letter should be "#".'));
    // }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $route = 'clashofclans_clan.search';
    $options = [
      'query' => array_filter([
        'name' => $form_state->getValue('name'),
        'warFrequency' => $form_state->getValue('warFrequency'),
        'minMembers' => $form_state->getValue('minMembers'),
        'minClanPoints' => $form_state->getValue('minClanPoints'),
        'minClanLevel' => $form_state->getValue('minClanLevel'),
        'limit' => $form_state->getValue('limit'),
      ]),
    ];
    $form_state->setRedirect($route, [], $options);
  }

}
