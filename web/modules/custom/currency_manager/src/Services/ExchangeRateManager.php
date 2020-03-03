<?php

namespace Drupal\currency_manager\Services;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Datetime\DrupalDateTime;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

class ExchangeRateManager {

  /**
   * Http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * ExchangeRateManager constructor.
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * The path.
   *
   * @var string
   */
  const NBU_EXCHANGE_PATH = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange';

  protected function apiRequest($path, $query = []) {
    if (!isset($query['json'])) {
      $query['json'] = '';
    }
    $response = $this->httpClient->request('GET', $path, ['query' => $query]);
    $content = $response->getBody()->getContents();

    return Json::decode($content);

  }

  public function getRates($date = NULL) {
    $query = [];
    if ($date instanceof DrupalDateTime) {
      $query['date'] = $date->format('Ymd');
    }
    $data = $this->apiRequest(self::NBU_EXCHANGE_PATH, $query);

    $result  = [];
    foreach ($data as $val) {
      $result[$val['cc']] = $val['rate'];
    }
    return $result;
  }

}
