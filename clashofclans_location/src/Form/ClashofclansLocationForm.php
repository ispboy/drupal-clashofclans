<?php

namespace Drupal\clashofclans_location\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the location entity edit forms.
 */
class ClashofclansLocationForm extends ContentEntityForm {

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
      $this->messenger()->addStatus($this->t('New location %label has been created.', $message_arguments));
      $this->logger('clashofclans_location')->notice('Created new location %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The location %label has been updated.', $message_arguments));
      $this->logger('clashofclans_location')->notice('Updated new location %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.clashofclans_location.canonical', ['clashofclans_location' => $entity->id()]);
  }

}
