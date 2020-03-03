<?php

namespace Drupal\currency_manager\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\currency_manager\CurrencyUtility;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'CurrencyRatesBlock' block.
 *
 * @Block(
 *  id = "currency_rates",
 *  admin_label = @Translation("Currency rates"),
 * )
 */
class CurrencyRatesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\currency_manager\Services\ExchangeRateManager definition.
   *
   * @var \Drupal\currency_manager\Services\ExchangeRateManager
   */
  protected $currencyManagerExchangeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->currencyManagerExchangeManager = $container->get('currency_manager.exchange_manager');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    $date = new DrupalDateTime('now', '+2:00');
    $day_seconds = 24 * 60 * 60;
    $tomorow_time = strtotime('tomorrow');
    $age = $tomorow_time - $date->getTimestamp();
    $week_day = $date->format('w');
    if ($week_day == 5) {
      $age += 2 * $day_seconds;
    }
    elseif ($week_day == 6) {
      $age += $day_seconds;
    }

    return $age;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $settings = parent::defaultConfiguration();

    $settings['currencies'] = [];
    $settings['amount_days'] = [];

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $settings = $this->getConfiguration();
    $amount_days = $settings['amount_days'];
    $date = new DrupalDateTime();
    $rates = [];
    $dates  = [];
    for ($i = 0; $i < $amount_days; $i++) {
      while ($date->format('w') == 0 || $date->format('w') == 6 ) {
        $date->modify('- 1 day');
      }
      $dates[] = $date->format('d/m');
      $rates[] = $this->currencyManagerExchangeManager->getRates($date);
      $date->modify('- 1 day');
    }
    $all_currency_names = CurrencyUtility::getCurrencyNameList();
    $currency_names = [];
    $currency_rates = [];
    foreach ($settings['currencies'] as $currency) {
      $currency_names[] = $all_currency_names[$currency];
      $currency_rates_item = [];
      foreach ($rates as $rates_day) {
        $currency_rates_item[] = round($rates_day[$currency],2);
      }
      $currency_rates[] = $currency_rates_item;
    }

    $build = [
      '#theme' => 'currency_rates_block',
      '#currency_names' => $currency_names,
      '#currency_rates' => $currency_rates,
      '#dates' => $dates,
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $settings = $this->getConfiguration();
    $form['currencies'] = [
      '#type' => 'checkboxes',
      '#options' => CurrencyUtility::getCurrencyNameList(),
      '#default_value' => $settings['currencies'],
      '#title' => $this->t('Select currency that you want to display!'),
    ];
    $form['amount_days'] = [
      '#type' => 'number',
      '#title' => $this
        ->t('Amount of previous days'),
      '#min' => 1,
      '#default_value' => $settings['amount_days'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $currencies = array_values(array_filter($form_state->getValue('currencies')));
    $this->configuration['currencies'] = $currencies;

    $amount_days = $form_state->getValue('amount_days');
    $this->configuration['amount_days'] = $amount_days;
  }

}

// Кодінг стадарти, вибір кількості днів, доробити вивід в твіг, зробити правильний кешінг на кінець доби оновлення
// заокруглити значення копійок
//round($rate, 2)


// вибір кількості днів на формі блока field number...


//max cache age дійсний до кінця дня
// якщо пятниця то докінця неділі
