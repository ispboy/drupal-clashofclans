<?php

namespace Drupal\clashofclans_location\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for a location entity type.
 */
class ClashofclansLocationSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'clashofclans_location_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['settings'] = [
      '#markup' => $this->t('Settings form for a location entity type.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('The configuration has been updated.'));
  }

}
