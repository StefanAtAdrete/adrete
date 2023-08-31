<?php

namespace Drupal\media_remote\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'media_remote_google' formatter.
 *
 * Google documentation:
 * https://support.google.com/docs/answer/183965#embed_files&zippy=%2Cedit-embedded-spreadsheets%2Cembed-a-document-spreadsheet-or-presentation .
 *
 * @FieldFormatter(
 *   id = "media_remote_google",
 *   label = @Translation("Remote Media - Google"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class MediaRemoteGoogleFormatter extends MediaRemoteFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function getUrlRegexPattern() {
    return '/^https:\/\/docs\.google\.com\/(document|spreadsheets|presentation)\/d\/e\//';
  }

  /**
   * {@inheritdoc}
   */
  public static function getValidUrlExampleStrings(): array {
    return [
      'https://docs.google.com/document/d/e/[your-document-hash]/pub',
      'https://docs.google.com/spreadsheets/d/e/[your-document-hash]/pubhtml',
      'https://docs.google.com/presentation/d/e/[your-document-hash]/pub?start=false&loop=false&delayms=3000',
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
      return t('Google document from @url', [
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
      $elements[$delta] = [
        '#theme' => 'media_remote_google',
        '#type' => $matches[1][0],
        '#url' => $item->value,
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
        'width' => 960,
        'height' => 600,
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return parent::settingsForm($form, $form_state) + [
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
