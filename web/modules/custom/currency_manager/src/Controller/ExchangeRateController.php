<?php

namespace Drupal\currency_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\currency_manager\Services\ExchangeRateManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BiointMessageController.
 */
class ExchangeRateController extends ControllerBase {

  /**
   * Exchange Manager;
   */
  protected $exchangeManager;

  /**
   * Constructor.
   *
   * @inheritdoc
   */
  public function __construct(ExchangeRateManager $exchange_manager) {
    $this->exchangeManager = $exchange_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('currency_manager.exchange_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function exchangeMethod() {
    $data = $this->exchangeManager->getRates();
    foreach($data as $key => $value)
    {
      return [
        '#type' => 'markup',
        '#markup' => "$key = $value",
      ];
    }
  }

}
