<?php

namespace Drupal\amazon_paapi;

/**
 * Amazon PA API service trait.
 */
trait AmazonPaapiTrait {

  /**
   * The Amazon PA API Service.
   *
   * @var \Drupal\amazon_paapi\AmazonPaapi
   */
  protected $amazonPaapi;

  /**
   * Gets the Amazon PA API Service.
   *
   * @return \Drupal\amazon_paapi\AmazonPaapi
   */
  public function getAmazonPaapi() {
    if (empty($this->amazonPaapi)) {
      $this->amazonPaapi = \Drupal::service('amazon_paapi.amazon_paapi');
    }
    return $this->amazonPaapi;
  }

}
