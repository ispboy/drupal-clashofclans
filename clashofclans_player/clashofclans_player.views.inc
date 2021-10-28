<?php
/**
 * Implements hook_views_data_alter().
 */
function clashofclans_player_views_data_alter(array &$data) {
  $data['users']['clashofclans_player_data'] = array(
    'title' => t('Player data'),
    'field' => array(
      'title' => t('Player Data'),
      'help' => t('Parse data from field_data'),
      'id' => 'clashofclans_api_data',
    ),
  );
}
