<?php

namespace Drupal\amazon_product_widget\Plugin\views\field;

use Drupal\amazon_product_widget\ProductStore;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Shows whether the product is available or not.
 *
 * @ViewsField("amazon_product_widget_product_available")
 *
 * @package Drupal\amazon_product_widget\Plugin\views\field
 */
class ProductAvailable extends FieldPluginBase {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public function render(ResultRow $values) {
    $value = intval($this->getValue($values));

    switch ($value) {
      case ProductStore::PRODUCT_AVAILABLE:
        $markup = ['#markup' => '<span class="color-success">' . $this->t('Yes') . '</span>'];
        break;

      case ProductStore::PRODUCT_NOT_AVAILABLE:
        $markup = ['#markup' => '<span class="color-warning">' . $this->t('No') . '</span>'];
        break;

      default:
        $markup = ['#markup' => '<span class="color-error">' . $this->t('Not found') . '</span>'];
        break;
    }

    return $markup;
  }

}
