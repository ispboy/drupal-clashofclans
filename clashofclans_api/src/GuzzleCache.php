<?php
namespace Drupal\clashofclans_api;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use Drupal\guzzle_cache\DrupalGuzzleCache;
use GuzzleHttp\Exception\RequestException;

class GuzzleCache {

  protected $key;
  protected $cacheMaxAge;
  protected $httpClient;

  /**
   * Class constructor.
   */
  public function __construct() {
    // Create default HandlerStack
    $stack = HandlerStack::create();

    // Create a Drupal Guzzle cache. Its' useful to have a separate cache bin to
    // manage independent of other cache bins. Here is how you might define such a
    // cache bin in a *.service.yml file:
    // cache.my_custom_http_cache_bin:
    //   class: Drupal\Core\Cache\CacheBackendInterface
    //   tags:
    //     - { name: cache.bin }
    //   factory: cache_factory:get
    //   arguments: [my_custom_http_cache_bin]
    $cache = new DrupalGuzzleCache(\Drupal::service('cache.clashofclans_api_http_cache_bin'));

    // Push the cache to the stack.
    $stack->push(
      new CacheMiddleware(
        new PrivateCacheStrategy($cache)
      ),
      'cache'
    );

    $config = \Drupal::config('clashofclans_api.settings');
    $this->key = $config->get('key');
    $this->cacheMaxAge = $config->get('cache_max_age');
    $base_uri = $config->get('base_uri');
    $this->httpClient = \Drupal::service('http_client_factory')->fromOptions([
      'base_uri' => $base_uri,
      'handler' => $stack,
    ]);;
  }

  /**
   * @param $url, $json: 'json', others.
   * @return array
   */
  public function get($url, $json = ''){
    $data = $this->request('GET', $url);
    return ($json == 'json')? $data: \Drupal\Component\Serialization\Json::decode($data);

  }

  /**
   * @param $url, $json: 'json', others.
   * @return array
   */
  public function request($method='GET', $url, $options=[]){
    $data = &drupal_static(__FUNCTION__);
    $cid = 'clashofclans:'. urlencode($url); //not the this->key;

    if (!isset($data[$cid])) {

      $data[$cid] = NULL;

      if ($cache = \Drupal::cache()->get($cid)) {
        $data[$cid] = $cache->data;
      } else {
        $age = $this->cacheMaxAge;
        try {
          $options['headers']['authorization'] = 'Bearer ' . $this->key;
          $response = $this->httpClient->request($method, $url, $options);
          $data[$cid] = $response->getBody()->getContents();
          \Drupal::cache()->set($cid, $data[$cid], time() + $age);
        }
        catch (RequestException $error) {
          if ($error->getCode() == 404) {
            \Drupal::cache()->set($cid, $data[$cid], time() + $age * 10);
          } else {
            $logger = \Drupal::logger('ClashOfClans API Request error');
            $logger->error($error->getMessage());
          }
        }
      }
    }

    return $data[$cid]; //json format.
  }
}
