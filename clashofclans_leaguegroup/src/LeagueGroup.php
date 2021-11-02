<?php
namespace Drupal\clashofclans_leaguegroup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\clashofclans_api\GuzzleCache;

class LeagueGroup {
  public $client;
  protected $entityTypeManager;

  public function __construct(
    GuzzleCache $client,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
      $this->client = $client;
      $this->entityTypeManager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container) {
      return new static(
        $container->get('clashofclans_api.guzzle_cache'),
        $container->get('entity_type.manager'),
      );
  }

  public function currentWar($entity) {
    if (isset($entity->tag->value)) {
      $tag = $entity->tag->value;
      $url = 'clans/'. $tag. '/currentwar/leaguegroup';
      $data = $this->client->getData($url);
      if ($data) {
        $league = $entity->get('field_warleague')->first()->get('entity')->getValue();
        if ($league) {
          $data['warLeague'] = [
            'id' => $league->id(),
            'name' => $league->label(),
          ];
        }
        return $data;
      }
    }
  }

}
