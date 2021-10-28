<?php

/**
 * Implements hook_views_data().
 */
function clashofclans_api_views_data() {
  $data = [];
  // Base data.
  $data['clashofclans_api']['table']['group'] = t('ClashOfClans');
  $data['clashofclans_api']['table']['base'] = [
    'title' => t('ClashOfClans API'),
    'help' => t('Game data provided by the ClashOfClans API.'),
    'query_id' => 'clashofclans_api_query',
  ];

  // Plain text field, exposed as a field, sort, filter, and argument.
  $data['clashofclans_api']['plain_text_field'] = [
    'title' => t('Plain text field'),
    'help' => t('The common field type of ClashOfClans.'),
    'field' => [
      // ID of field handler plugin to use.
      'id' => 'clashofclans_api_field',
    ],
  ];

  // Numeric field, exposed as a field, sort, filter, and argument.
  $data['clashofclans_api']['numeric_field'] = [
    'title' => t('Numeric field'),
    'help' => t('Just a numeric field.'),
    'field' => [
      // ID of field handler plugin to use.
      'id' => 'clashofclans_api_field',
    ],
  ];

  // Contextual filter required to replace the "%" placeholder in the url.
  $data['clashofclans_api']['location_id'] = [
    'title' => t('Location Id.'),
    'help' => t('Replacement for placeholder in URL.'),
    'argument' => [
      'id' => 'clashofclans_api_location_id',
    ],
  ];
  return $data;
}
