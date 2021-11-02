<?php

namespace Drupal\clashofclans_warleague\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the warleague entity edit forms.
 */
class ClashofclansWarleagueForm extends ContentEntityForm {

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
      $this->messenger()->addStatus($this->t('New warleague %label has been created.', $message_arguments));
      $this->logger('clashofclans_warleague')->notice('Created new warleague %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The warleague %label has been updated.', $message_arguments));
      $this->logger('clashofclans_warleague')->notice('Updated new warleague %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.clashofclans_warleague.canonical', ['clashofclans_warleague' => $entity->id()]);
  }

}
