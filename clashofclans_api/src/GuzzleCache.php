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

  protected $key; // the clash of clans api key.
  protected $http_client;
  protected $cache_max_age;
  protected $api_root;
  protected $csrf_token;

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

    $config = $config_factory->get('clashofclans_api.settings');
    $this->key = $config->get('key');

    $this->cache_max_age = $config->get('cache_max_age');
    $this->api_root = $config->get('api_root');
    $this->csrf_token = \Drupal::getContainer()->get('csrf_token')
                                               ->get(ltrim($this->api_root, '/'));

    // Push the cache to the stack.
    $stack->push(
      new CacheMiddleware(
        new PrivateCacheStrategy($cache)
      ),
      'cache'
    );

    $base_uri = $config->get('base_uri');
    $this->http_client = \Drupal::service('http_client_factory')->fromOptions([
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
  public function getData($url, $options = []){
    $data = $this->getJson($url, $options);
    return \Drupal\Component\Serialization\Json::decode($data);
  }

  /**
   * @param $url, $options
   * @return string
   */
  public function getJson($url, $options = []){
    $data = $this->request('GET', $url, $options);
    return $data;
  }

  /**
   * @param $url, $json: 'json', others.
   * @return array
   */
  public function postData($url, $options = []){
    $data = $this->request('POST', $url, $options);
    if ($data) {
      return \Drupal\Component\Serialization\Json::decode($data);
    }
  }

  public function request($method, $url, $options = []) {
    $url = \str_replace('#', '%23', $url);
    $options['headers']['authorization'] = 'Bearer ' . $this->key;
    try {
      $response = $this->http_client->request($method, $url, $options);
      $data = $response->getBody()->getContents();
      return $data;
    } catch (RequestException $error) {
      $message = $error->getMessage();
      $code = $error->getCode();
      // to be continute..
    }
  }

  public function getCsrfToken() {
    if (isset($this->csrf_token)) {
      return $this->csrf_token;
    }
  }

  public function getMaxAge() {
    if (isset($this->cache_max_age)) {
      return $this->cache_max_age;
    }
  }
  
}
