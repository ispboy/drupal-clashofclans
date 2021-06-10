<?php

namespace Drupal\clashofclans_api;

use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Link;
use Drupal\Core\Url;

class Client {

  protected $key;
  protected $cacheMaxAge;
  protected $httpClient;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->key = \Drupal::config('clashofclans_api.settings')->get('key');
    $this->cacheMaxAge = \Drupal::config('clashofclans_api.settings')->get('cache_max_age');

    $base_uri = \Drupal::config('clashofclans_api.settings')->get('base_uri');
    $this->httpClient = \Drupal::service('http_client_factory')->fromOptions([
      'base_uri' => $base_uri,
    ]);;
  }

  /**
   * @param $url, $json: 'json', others.
   * @return array
   */
  public function get($url, $json = ''){
    $data = &drupal_static(__FUNCTION__);
    $key = urlencode($url);

    if (!isset($data[$key])) {

      $data[$key] = NULL;

        try {
          $options = [
            'headers' => ['authorization' => 'Bearer ' . $this->key],
          ];
          $response = $this->httpClient->request('GET', $url, $options);
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

    return ($json == 'json')? $data[$key]: \Drupal\Component\Serialization\Json::decode($data[$key]);

  }

  public function getCacheMaxAge() {
    return $this->cacheMaxAge;
  }

}
