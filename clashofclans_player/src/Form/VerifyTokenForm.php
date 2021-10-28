<?php

namespace Drupal\clashofclans_player\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans_player\Player;
use Drupal\Core\Messenger\Messenger;

/**
 * Provides a ClashOfClans Verify token form.
 */
class VerifyTokenForm extends FormBase {

  protected $player;
  protected $messenger;

  public function __construct(Player $player, Messenger $messenger) {
      $this->player = $player;
      $this->messenger = $messenger;
  }

  public static function create(ContainerInterface $container) {
      return new static(
        $container->get('clashofclans_player.player'),
        $container->get('messenger'),
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'clashofclans_player_verifytoken_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $tag=NULL) {

    $form['player_tag'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tag of the player'),
      '#size' => 15,
      '#default_value' => $tag,
      '#required' => TRUE,
    ];

    $form['api_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Token of the player'),
      '#size' => 15,
      '#description' => $this->t('Verify player API token that can be found from the game settings. This API call can be used to check that players own the game accounts they claim to own as they need to provide the one-time use API token that exists inside the game.'),
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
    // if (mb_substr($form_state->getValue('player_tag'), 0, 1) <> '#') {
    //   $form_state->setErrorByName('player_tag', $this->t('The first letter should be "#".'));
    // }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // $route = 'clashofclans_player.tag';
    $tag = '#'. ltrim($form_state->getValue('player_tag'), '#');
    $token = $form_state->getValue('api_token');
    $status = '';
    $uid = $this->player->verifyToken($tag, $token, $status);
    if ($status) {
      switch ($status) {
        case 'invalid':
          $this->messenger->addWarning($this->t('API token invalid.'));
          break;
        case 'ok';
          $this->messenger->addStatus($this->t('Welcome home!'));
          $form_state->setRedirect('entity.user.canonical', ['user' => $uid]);
          break;
      }
    }
  }

}
