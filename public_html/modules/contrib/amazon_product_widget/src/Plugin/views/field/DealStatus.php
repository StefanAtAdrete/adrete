<?php

namespace Drupal\amazon_product_widget\Plugin\views\field;

use Drupal\amazon_product_widget\DealFeedService;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Renders deal status in human readable format.
 *
 * @ViewsField("amazon_product_widget_deal_status")
 *
 * @package Drupal\amazon_product_widget\Plugin\views\field
 */
class DealStatus extends FieldPluginBase {

  use StringTranslationTrait;

  /**
   * Deal feed service.
   *
   * @var \Drupal\amazon_product_widget\DealFeedService
   */
  protected $dealFeedService;

  /**
   * DealStatus constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\amazon_product_widget\DealFeedService $dealFeedService
   *   Deal feed service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    DealFeedService $dealFeedService
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dealFeedService = $dealFeedService;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('amazon_product_widget.deal_feed_service')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function render(ResultRow $values) {
    $status = $this->getValue($values);

    if ($status === '' || $status === NULL) {
      return [
        '#markup' => $this->t('Unavailable'),
      ];
    }
    else {
      return $this->dealFeedService->getDealStore()->statusToString($status);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function getValue(ResultRow $values, $field = NULL) {
    return parent::getValue($values, $field) ?? '';
  }

}
