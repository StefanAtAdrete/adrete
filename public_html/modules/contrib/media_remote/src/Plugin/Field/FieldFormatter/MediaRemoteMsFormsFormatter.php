<?php

namespace Drupal\media_remote\Plugin\Field\FieldFormatter;

use Drupal\Core\Url;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'media_remote_msforms' formatter.
 *
 * (Unofficial) documentation:
 * https://web.fresnounified.org/wordpress/embedding-a-microsoft-form/
 *
 * @FieldFormatter(
 *   id = "media_remote_msforms",
 *   label = @Translation("Remote Media - Microsoft Forms"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class MediaRemoteMsFormsFormatter extends MediaRemoteFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function getUrlRegexPattern() {
    return '/^https:\/\/forms\.office\.com\/((r\/[\w-]+)|(Pages\/ResponsePage\.aspx\?id=[\w-]+))$/';
  }

  /**
   * {@inheritdoc}
   */
  public static function getValidUrlExampleStrings(): array {
    return [
      // Regular form.
      'https://forms.office.com/Pages/ResponsePage.aspx?id=foobar-aBcDxYZ7890',
      // Shortened form.
      'https://forms.office.com/r/123456ABCD',
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
      return t('Microsoft Form from @url', [
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
      // Add "embed=true" to the form's query parameters.
      $url_parsed = UrlHelper::parse($item->value);
      $url_parsed['query']['embed'] = 'true';
      $url = Url::fromUri($url_parsed['path'], $url_parsed)->toString();
      // Width and height may include HTML tags as there is no validation in the
      // settings form ; this allows the user to provide values either as pixels
      // or as percentages.
      // This means we need to we need to sanitize those values.
      $elements[$delta] = [
        '#theme' => 'media_remote_msforms',
        '#url' => $url,
        '#width' => Html::escape($this->getSetting('width') ?? '640px'),
        '#height' => Html::escape($this->getSetting('height') ?? '480px'),
      ];
    }
    return $elements;
  }


  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'width' => '640px',
      'height' => '480px',
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
        '#size' => 10,
        '#maxlength' => 10,
        '#description' => $this->t('Iframe width, in pixels or as a percentage of the available area. Examples: 640px, 100%.'),
      ],
      'height' => [
        '#type' => 'textfield',
        '#title' => $this->t('Height'),
        '#default_value' => $this->getSetting('height'),
        '#size' => 10,
        '#maxlength' => 10,
        '#description' => $this->t('Iframe height, in pixels or as a percentage of the available area. Examples: 480px, 100%.'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Iframe size: %width x %height', [
      '%width' => $this->getSetting('width'),
      '%height' => $this->getSetting('height'),
    ]);
    return $summary;
  }

}
