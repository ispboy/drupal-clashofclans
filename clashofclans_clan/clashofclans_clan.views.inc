<?php
/**
 * Implements hook_views_data_alter().
 */
function clashofclans_clan_views_data_alter(array &$data) {
  $data['clashofclans_clan']['clashofclans_clan_data'] = array(
    'title' => t('Clan data'),
    'field' => array(
      'title' => t('Clan Data'),
      'help' => t('Parse data from field_data'),
      'id' => 'clashofclans_api_data',
    ),
  );
}
