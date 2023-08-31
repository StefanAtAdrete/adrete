<?php

namespace Drupal\media_remote\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'media_remote_brightcove' formatter.
 *
 * @FieldFormatter(
 *   id = "media_remote_brightcove",
 *   label = @Translation("Remote Media - Brightcove"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class MediaRemoteBrightcoveFormatter extends MediaRemoteFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function getUrlRegexPattern() {
    return '/^https:\/\/players\.brightcove\.net\/([\d]+)\/[a-zA-Z0-9-]+_default\/index\.html\?videoId=([\d]+)/';
  }

  /**
   * {@inheritdoc}
   */
  public static function getValidUrlExampleStrings(): array {
    return [
      'https://players.brightcove.net/[account-id]/default_default/index.html?videoId=[video-id]',
      'https://players.brightcove.net/[account-id]/abc-ABC-123_default/index.html?videoId=[video-id]',
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
      $elements[$delta] = [
        '#theme' => 'media_remote_brightcove',
        '#url' => $item->value,
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
      return t('Brightcove video @url', [
        '@url' => $url,
      ]);
    }
    return parent::deriveMediaDefaultNameFromUrl($url);
  }

}
