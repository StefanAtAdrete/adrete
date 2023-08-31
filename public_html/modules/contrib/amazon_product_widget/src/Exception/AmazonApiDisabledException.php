<?php

namespace Drupal\amazon_product_widget\Exception;

/**
 * Amazon API disabled.
 */
class AmazonApiDisabledException extends AmazonServiceException {

  /**
   * {@inheritDoc}
   */
  public function __construct(
    $message = "Amazon Api endpoint disabled via config setting `amazon_product_widget.settings.amazon_api_disabled`.",
    $code = 0,
    \Throwable $previous = NULL
  ) {}

}
