<?php

namespace Drupal\clashofclans_player\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the player entity edit forms.
 */
class ClashofclansPlayerForm extends ContentEntityForm {

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
      $this->messenger()->addStatus($this->t('New player %label has been created.', $message_arguments));
      $this->logger('clashofclans_player')->notice('Created new player %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The player %label has been updated.', $message_arguments));
      $this->logger('clashofclans_player')->notice('Updated new player %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.clashofclans_player.canonical', ['clashofclans_player' => $entity->id()]);
  }

}
