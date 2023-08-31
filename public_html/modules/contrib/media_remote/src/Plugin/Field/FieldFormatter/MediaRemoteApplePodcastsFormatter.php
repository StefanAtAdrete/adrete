<?php

namespace Drupal\media_remote\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'media_remote_apple_podcasts' formatter.
 *
 * @FieldFormatter(
 *   id = "media_remote_apple_podcasts",
 *   label = @Translation("Remote Media - Apple Podcasts"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class MediaRemoteApplePodcastsFormatter extends MediaRemoteFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function getUrlRegexPattern() {
    return '/^https:\/\/podcasts\.apple\.com\/us\/podcast\/([a-z-]+.*)\/(.+)/';
  }

  /**
   * {@inheritdoc}
   */
  public static function getValidUrlExampleStrings(): array {
    return [
      'https://podcasts.apple.com/us/podcast/[episode-title]/[id]?i=[token]'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function deriveMediaDefaultNameFromUrl($url) {
    $matches = [];
    $pattern = static::getUrlRegexPattern();
    preg_match_all($pattern, $url, $matches);
    if (!empty($matches[1][0]) && !empty($matches[2][0])) {
      $slug = $matches[1][0];
      return ucfirst(trim(str_replace("-", " ", $slug)));
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
      if (empty($matches[1][0]) || empty($matches[2][0])) {
        continue;
      }
      $slug = $matches[1][0];
      $token = $matches[2][0];
      $elements[$delta] = [
        '#theme' => 'media_remote_apple_podcasts',
        '#slug' => $slug,
        '#token' => $token,
      ];
    }
    return $elements;
  }

}
