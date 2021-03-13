<?php

namespace Drupal\clashofclans_clan\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the clan entity edit forms.
 */
class ClashofclansClanForm extends ContentEntityForm {

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
      $this->messenger()->addStatus($this->t('New clan %label has been created.', $message_arguments));
      $this->logger('clashofclans_clan')->notice('Created new clan %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The clan %label has been updated.', $message_arguments));
      $this->logger('clashofclans_clan')->notice('Updated new clan %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.clashofclans_clan.canonical', ['clashofclans_clan' => $entity->id()]);
  }

}
