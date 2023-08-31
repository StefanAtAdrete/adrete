<?php

namespace Drupal\media_remote\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'media_remote_libsyn' formatter.
 *
 * @FieldFormatter(
 *   id = "media_remote_libsyn",
 *   label = @Translation("Remote Media - Libsyn"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class MediaRemoteLibsynFormatter extends MediaRemoteFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function getUrlRegexPattern() {
    return '/^https:\/\/directory\.libsyn\.com\/episode\/index\/id\/([\d]+)/';
  }

  /**
   * {@inheritdoc}
   */
  public static function getValidUrlExampleStrings(): array {
    return [
      'https://directory.libsyn.com/episode/index/id/20026970',
    ];
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
      $episode_id = $matches[1][0];
      $elements[$delta] = [
        '#theme' => 'media_remote_libsyn',
        '#episode_id' => $episode_id,
      ];
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function deriveMediaDefaultNameFromUrl($url) {
    $pattern = static::getUrlRegexPattern();
    if (preg_match($pattern, $url)) {
      return t('Libsyn episode from @url', [
        '@url' => $url,
      ]);
    }
    return parent::deriveMediaDefaultNameFromUrl($url);
  }

}
