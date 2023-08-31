<?php

declare(strict_types=1);

namespace Drupal\media_remote\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'media_remote_arcgis' formatter.
 *
 * @FieldFormatter(
 *   id = "media_remote_arcgis",
 *   label = @Translation("Remote Media - ArcGIS"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class MediaRemoteArcGISFormatter extends MediaRemoteFormatterBase {

  const PUBLIC_DOMAIN = "https://arcgis.com";

  /**
   * {@inheritdoc}
   */
  public static function getUrlRegexPattern() {
    return '/^https:\/\/([\w\.+]*)arcgis\.com\/apps\/(dashboards|mapviewer|Embed|View)\/(?:index\.html\?webmap=)?([\w\d\-]+)([\S]*)/';

  }

  /**
   * {@inheritdoc}
   */
  public static function getValidUrlExampleStrings(): array {
    return [
      self::PUBLIC_DOMAIN . '/apps/mapviewer/index.html?webmap=[arcgis-id]',
      self::PUBLIC_DOMAIN . '/apps/Embed/index.html?webmap=[arcgis-id]',
      self::PUBLIC_DOMAIN . '/apps/dashboards/[arcgis-id]',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function deriveMediaDefaultNameFromUrl($url) {
    $matches = [];
    $pattern = static::getUrlRegexPattern();
    preg_match_all($pattern, $url, $matches);
    if (!empty($matches[1][0])) {
      return t('ArcGIS view from @url', [
        '@url' => $url,
      ]);
    }
    return parent::deriveMediaDefaultNameFromUrl($url);
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      /** @var \Drupal\Core\Field\FieldItemInterface $item */
      if ($item->isEmpty()) {
        continue;
      }
      $matches = [];
      $pattern = static::getUrlRegexPattern();
      preg_match_all($pattern, $item->value, $matches);

      if (empty($matches[1][0])) {
        continue;
      }
      $url_parsed = UrlHelper::parse($item->value);
      $url = Url::fromUri($url_parsed['path'], $url_parsed)->toString();

      $elements[$delta] = [
        '#theme' => 'media_remote_arcgis',
        '#url' => $url,
        '#name' => $item->getParent()->getParent()->get('name')->value,
        '#width' => $this->getSetting('width'),
        '#height' => $this->getSetting('height'),
      ];
    }
    return $elements;
  }


  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'width' => '100%',
        'height' => '400px',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return parent::settingsForm($form, $form_state) + [
        'width' => [
          '#type' => 'textfield',
          '#title' => $this->t('Width'),
          '#default_value' => $this->getSetting('width'),
          '#size' => 5,
          '#maxlength' => 10,
          '#description' => $this->t('Valid css unit e.g. 500px, 100%, etc.'),
        ],
        'height' => [
          '#type' => 'textfield',
          '#title' => $this->t('Height'),
          '#default_value' => $this->getSetting('height'),
          '#size' => 5,
          '#maxlength' => 10,
          '#description' => $this->t('Valid css unit e.g. 500px, 100%, etc.'),
        ],
      ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Iframe size: %width , %height.', [
      '%width' => $this->getSetting('width'),
      '%height' => $this->getSetting('height'),
    ]);
    return $summary;
  }
}
