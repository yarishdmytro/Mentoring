<?php

namespace Drupal\currency_manager;

class CurrencyUtility {

  public static function getCurrencyNameList() {
    return [
      'USD' => t('United States dollar'),
      'EUR' => t('European euro'),
      'CAD' => t('Canadian dollar'),
      'CHF' => t('Swiss franc'),
      'RUB' => t('Russian ruble'),
    ];
  }
}
