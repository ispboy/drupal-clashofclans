<?php

namespace Drupal\clashofclans_war\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the war entity edit forms.
 */
class ClashofclansWarForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $entity = $this->getEntity();
    $result = $entity->save();
    $link = $entity->toLink($this->t('View'))->toRenderable();

    $message_arguments = ['%label' => $this->entity->label()];
    $logger_arguments = $message_arguments + ['link' => render($link)];

    if ($result == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('New war %label has been created.', $message_arguments));
      $this->logger('clashofclans_war')->notice('Created new war %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The war %label has been updated.', $message_arguments));
      $this->logger('clashofclans_war')->notice('Updated new war %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.clashofclans_war.canonical', ['clashofclans_war' => $entity->id()]);
  }

}
