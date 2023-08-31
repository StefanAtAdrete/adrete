<?php

namespace Drupal\media_remote\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'media_remote_dropbox' formatter.
 *
 * Dropbox documentation:
 * https://www.dropbox.com/developers/embedder .
 *
 * @FieldFormatter(
 *   id = "media_remote_dropbox",
 *   label = @Translation("Remote Media - Dropbox"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class MediaRemoteDropboxFormatter extends MediaRemoteFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function getUrlRegexPattern() {
    return '/^https:\/\/www\.dropbox\.com\/s\/[^\/]+\/[^\/]+\?dl=0$/';
  }

  /**
   * {@inheritdoc}
   */
  public static function getValidUrlExampleStrings(): array {
    return [
      'https://www.dropbox.com/s/u0bdwmkjmqld9l2/dbx-supporting-distributed-work.gif?dl=0',
      'https://www.dropbox.com/s/ttqabn104bujvcv/Primeros%20pasos%20con%20Dropbox.pdf?dl=0',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function deriveMediaDefaultNameFromUrl($url) {
    $pattern = static::getUrlRegexPattern();
    if (preg_match($pattern, $url)) {
      return t('Dropbox document from @url', [
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
      $pattern = static::getUrlRegexPattern();
      if (!preg_match($pattern, $item->value)) {
        continue;
      }
      $elements[$delta] = [
        '#theme' => 'media_remote_dropbox',
        '#url' => $item->value,
        '#app_key' => $this->getSetting('app_key') ?? '',
        '#width' => $this->getSetting('width') ?? 960,
        '#height' => $this->getSetting('height') ?? 600,
      ];
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'app_key' => '',
        'width' => 960,
        'height' => 600,
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return parent::settingsForm($form, $form_state) + [
        'app_key' => [
          '#type' => 'textfield',
          '#title' => $this->t('App Key'),
          '#description' => $this->t('Refer to the <a href="@url">Dropbox documentation</a> for more information on how to generate and secure your App key.', [
            '@url' => 'https://www.dropbox.com/developers/embedder',
          ]),
          '#default_value' => $this->getSetting('app_key') ?? '',
          '#required' => TRUE,
        ],
        'width' => [
          '#type' => 'number',
          '#title' => $this->t('Width'),
          '#default_value' => $this->getSetting('width'),
          '#size' => 5,
          '#maxlength' => 5,
          '#field_suffix' => $this->t('pixels'),
          '#min' => 50,
        ],
        'height' => [
          '#type' => 'number',
          '#title' => $this->t('Height'),
          '#default_value' => $this->getSetting('height'),
          '#size' => 5,
          '#maxlength' => 5,
          '#field_suffix' => $this->t('pixels'),
          '#min' => 50,
        ],
      ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Iframe size: %width x %height pixels', [
      '%width' => $this->getSetting('width'),
      '%height' => $this->getSetting('height'),
    ]);
    return $summary;
  }

}
