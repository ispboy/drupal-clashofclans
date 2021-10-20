<?php
namespace Drupal\clashofclans_api;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use Drupal\guzzle_cache\DrupalGuzzleCache;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

class GuzzleCache implements ContainerInjectionInterface {

  protected $key;
  protected $cacheMaxAge;
  protected $httpClient;

  /**
   * Class constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
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

    $config = $config_factory->get('clashofclans_api.settings');
    $this->key = $config->get('key');
    $this->cacheMaxAge = $config->get('cache_max_age');
    $base_uri = $config->get('base_uri');
    $this->httpClient = \Drupal::service('http_client_factory')->fromOptions([
      'base_uri' => $base_uri,
      'handler' => $stack,
    ]);;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * @param $url, $options
   * @return string
   */
  public function get($url, $options = [], $json = ''){
    $options['headers']['authorization'] = 'Bearer ' . $this->key;
    $response = $this->httpClient->get($url, $options);
    $data = $response->getBody()->getContents();
    return ($json == 'json') ? $data : \Drupal\Component\Serialization\Json::decode($data);
  }

  /**
   * @param $url, $json: 'json', others.
   * @return array
   */
  public function post($url, $body, $json = ''){
    $options['body'] = $body;
    $response = $this->httpClient->request('POST', $url, $options);
    $data = $response->getBody()->getContents();

    return ($json == 'json')? $data: \Drupal\Component\Serialization\Json::decode($data);

  }

}
