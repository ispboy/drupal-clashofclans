<?php

namespace Drupal\clashofclans_player\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\clashofclans\ClashofclansClient;
/**
 * Defines the 'clashofclans_player' field widget.
 *
 * @FieldWidget(
 *   id = "clashofclans_player",
 *   label = @Translation("Player"),
 *   field_types = {"clashofclans_player"},
 * )
 */
class PlayerWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  protected $client;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, ClashofclansClient $client) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'],
       $configuration['third_party_settings'], $container->get('clashofclans.client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    // $element['name'] = [
    //   '#type' => 'textfield',
    //   '#title' => $this->t('Name'),
    //   '#default_value' => isset($items[$delta]->name) ? $items[$delta]->name : NULL,
    //   '#size' => 20,
    // ];

    $element['tag'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tag'),
      '#default_value' => isset($items[$delta]->tag) ? $items[$delta]->tag : NULL,
      '#size' => 20,
    ];

    if (isset($items[$delta]->name)) {
      $element['name'] = [
        // '#type' => 'item',
        // '#title' => $this->t('Name'),
        '#markup' => isset($items[$delta]->name) ? $items[$delta]->name : NULL,
      ];
    }

    if ($items[$delta]->verified != 1) {
      $element['token'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Token'),
        '#default_value' => isset($items[$delta]->token) ? $items[$delta]->token : NULL,
        '#size' => 20,
        '#description' => $this->t('the API token of the player (optional). '),
      ];
    }
    // if (isset($items[$delta]->verified)) {
    //   $element['verified'] = [
    //     '#type' => 'hidden',
    //     '#value' => $items[$delta]->verified,
    //     // '#title' => $this->t('Name'),
    //     // '#markup' => isset($items[$delta]->verified) ? $items[$delta]->verified : NULL,
    //   ];
    // }


    $element['#theme_wrappers'] = ['container', 'form_element'];
    $element['#attributes']['class'][] = 'container-inline';
    $element['#attributes']['class'][] = 'clashofclans-player-elements';
    $element['#attached']['library'][] = 'clashofclans_player/clashofclans_player';

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    return isset($violation->arrayPropertyPath[0]) ? $element[$violation->arrayPropertyPath[0]] : $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $delta => $value) {
      // if ($value['name'] === '') {
      //   $values[$delta]['name'] = NULL;
      // }
      if ($value['tag'] === '') {
        $values[$delta]['tag'] = NULL;
        $values[$delta]['name'] = NULL;
        $values[$delta]['token'] = NULL;
        $values[$delta]['verified'] = NULL;
      } else {
        $tag = $value['tag'];
        $player = $this->client->get('getPlayer', ['tag' => $tag]);
        $name = $player->name();
        if ($name) {
          $values[$delta]['name'] = $name;
        }
        if (isset($value['token']) && $value['token']) {
          $verified = $this->client->get('verifyPlayer', ['tag' => $tag, 'token' => $value['token']]);
          // $verified = 1;
          if ($verified) {
            $values[$delta]['verified'] = 1;
          }
        }
      }
    }
    return $values;
  }

}
