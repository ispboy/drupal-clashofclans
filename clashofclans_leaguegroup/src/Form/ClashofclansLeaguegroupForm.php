<?php

namespace Drupal\clashofclans_leaguegroup\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the leaguegroup entity edit forms.
 */
class ClashofclansLeaguegroupForm extends ContentEntityForm {

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
      $this->messenger()->addStatus($this->t('New leaguegroup %label has been created.', $message_arguments));
      $this->logger('clashofclans_leaguegroup')->notice('Created new leaguegroup %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The leaguegroup %label has been updated.', $message_arguments));
      $this->logger('clashofclans_leaguegroup')->notice('Updated new leaguegroup %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.clashofclans_leaguegroup.canonical', ['clashofclans_leaguegroup' => $entity->id()]);
  }

}
