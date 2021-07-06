<?php

namespace Drupal\clashofclans_api;

use GuzzleHttp\Exception\RequestException;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

class Client implements ContainerInjectionInterface {

  protected $key;
  protected $cacheMaxAge;
  protected $httpClient;

  /**
   * Class constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $config = $config_factory->get('clashofclans_api.settings');
    $this->key = $config->get('key');
    $this->cacheMaxAge = $config->get('cache_max_age');
    $base_uri = $config->get('base_uri');
    $this->httpClient = \Drupal::service('http_client_factory')->fromOptions([
      'base_uri' => $base_uri,
    ]);;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('config.factory')
    );
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
  public function post($url, $body, $json = ''){
    $options['body'] = $body;
    $data = $this->request('POST', $url, $options);

    return ($json == 'json')? $data: \Drupal\Component\Serialization\Json::decode($data);

  }

  /**
   * @param $url, $json: 'json', others.
   * @return array
   */
  public function request($method='GET', $url, $options=[]){
    $data = &drupal_static(__FUNCTION__);
    $key = urlencode($url); //not the this->key;

    if (!isset($data[$key])) {

      $data[$key] = NULL;

        try {
          $options['headers']['authorization'] = 'Bearer ' . $this->key;
          $response = $this->httpClient->request($method, $url, $options);
          $data[$key] = $response->getBody()->getContents();
        }
        catch (RequestException $error) {
          if ($error->getCode() == 404) {
          } else {
            $logger = \Drupal::logger('ClashOfClans API Request error');
            $logger->error($error->getMessage());
          }
        }
    }

    return $data[$key]; //json format.
  }

  public function getCacheMaxAge() {
    return $this->cacheMaxAge;
  }

  public function strToDatetime($str) {
    $time_str = str_replace('.000Z', ' UTC', $str);
    return date('Y-m-d\TH:i:s', strtotime($time_str));    
  }
}
