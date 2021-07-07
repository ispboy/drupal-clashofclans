<?php
/**
*  Process data from League Group
*  Connect to drupal entity
**/
namespace Drupal\clashofclans_api;

use Drupal\clashofclans_api\Client;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\clashofclans_api\Clan;
use Drupal\Core\Datetime\DrupalDateTime;

class LeagueGroup {
  protected $client;
  protected $entityTypeManager;
  protected $clan;
  protected $data; //$data[$tag]
  protected $clans; //$clans[$tag] = ['nid' => [], 'nid' => [], ...]
  protected $rounds;

  public function __construct(Client $client, EntityTypeManagerInterface $entityTypeManager, Clan $clan) {
    $this->client = $client;
    $this->entityTypeManager = $entityTypeManager;
    $this->clan = $clan;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('clashofclans_api.client'),
      $container->get('entity_type.manager'),
      $container->get('clashofclans_api.clan')
    );
  }

  protected function connect($tag) {
    $url = 'clans/'. urlencode($tag). '/currentwar/leaguegroup';
    $data = $this->client->get($url);
    if (isset($data['season'])) {
      $this->data[$tag] = $data;
      $this->clans[$tag] = $this->getClans($tag);
      // $this->id = $this->getEntityId();
      return TRUE;
    }
  }

  protected function getClans($tag) {
    $data = $this->data[$tag];
    if (isset($data['clans'])) {
      $clans = $data['clans'];
      $tags = [];
      $entityClans = [];
      foreach ($clans as $clan) {
        $tag = $clan['tag'];
        $id = $this->clan->getEntityId($tag);
        $entityClans[$id] = [
          'tag' => $tag,
          'description' => $this->clan->getDescription($tag),
          'name' => $clan['name'],
          'clanLevel' => $clan['clanLevel'],
          'badgeUrls' => $clan['badgeUrls'],
        ];
      }
      return $entityClans;
    }

  }

  public function getEntity($tag) {
    if (!isset($this->data[$tag])) {
      $this->connect($tag);
    }
    if (isset($this->data[$tag])) {
      $data = $this->data[$tag];
      $season = $this->seasonToDate($data['season']);
      $clan_ids = array_keys($this->clans[$tag]);
      $storage = $this->entityTypeManager->getStorage('leaguegroup');
      $query = $storage->getQuery();
      $query -> condition('field_clan', $clan_ids, 'in');
      $query -> condition('season', $season);
      $ids = $query->execute();
      if ($ids) {
        $id = current($ids);
        $entity = $storage->load($id);
        return $entity;
      } else {
        $title = $this->clan->getName($tag). '('. $data['season']. ')';
        $entity = $storage->create([
          'title' => $title,
          'season' => [$season],
          'state' => $data['state'],
          'data' => \Drupal\Component\Serialization\Json::encode($data),
          'field_clan' => $clan_ids,
          'uid' => 1,
        ]);
        $entity->save();
        return $entity;
      }
    }
  }

  public function seasonToDate($season) {
    $season .= ' UTC';
    $datetime = DrupalDateTime::createFromTimestamp(strtotime($season), 'UTC');
    return $datetime->format("Y-m-d");
  }
}
