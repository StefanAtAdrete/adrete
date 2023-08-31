<?php

namespace Drupal\amazon_product_widget\Plugin\Field\FieldFormatter;

use Drupal\amazon_product_widget\ProductServiceTrait;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the Amazon Product Widget field formatter.
 *
 * @FieldFormatter(
 *   id = "amazon_product_widget_field_formatter",
 *   module = "amazon_product_widget",
 *   label = @Translation("Amazon Product Widget"),
 *   field_types = {
 *     "amazon_product_widget_field_type"
 *   }
 * )
 */
class AmazonProductFieldFormatter extends FormatterBase {

  use ProductServiceTrait;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private RouteMatchInterface $routeMatch;

  /**
   * Constructs a FormatterBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    RouteMatchInterface $route_match
  ) {
    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $label,
      $view_mode,
      $third_party_settings
    );
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    /** @var \Drupal\amazon_product_widget\Plugin\Field\FieldType\AmazonProductField $field */
    $field = $items->first();

    if (empty($field)) {
      return [];
    }

    $node = $this->routeMatch->getParameter('node');
    $build = [
      '#theme' => 'amazon_product_widget',
      '#attached' => [
        'library' => [
          'amazon_product_widget/amazon_product_widget',
        ],
      ],
      '#node_id' => $node ? $node->id() : NULL,
      '#entity_id' => $field->getEntity()->id(),
      '#entity_type' => $field->getEntity()->getEntityTypeId(),
      '#bundle' => $field->getEntity()->bundle(),
      '#field' => $field->getParent()->getName(),
    ];

    if ($this->getSetting('render_inline')) {
      $build['#products'] = $this->getProductService()
        ->buildProductsWithFallback($field);
    }

    CacheableMetadata::createFromObject($items->getEntity())->applyTo($build);
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return parent::defaultSettings() + [
      'render_inline' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['render_inline'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Render element inline.'),
      '#default_value' => $this->getSetting('render_inline'),
    ];

    return $form;
  }

}
