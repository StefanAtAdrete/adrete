<?php

namespace Drupal\media_remote\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'media_remote_buzzsprout' formatter.
 *
 * Buzzsprout documentation:
 * https://www.buzzsprout.com/help/16-placing-embed-code .
 *
 * @FieldFormatter(
 *   id = "media_remote_buzzsprout",
 *   label = @Translation("Remote Media - Buzzsprout"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class MediaRemoteBuzzsproutFormatter extends MediaRemoteFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function getUrlRegexPattern() {
    return '/^https:\/\/www\.buzzsprout\.com\/([\d]+)\/([\d]+)(-*.*)/';
  }

  /**
   * {@inheritdoc}
   */
  public static function getValidUrlExampleStrings(): array {
    return [
      'https://www.buzzsprout.com/123/456',
      'https://www.buzzsprout.com/123/456-foo-bar',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function deriveMediaDefaultNameFromUrl($url) {
    $matches = [];
    // Buzzsprout URLs can either contain only podcast + episode ID, or
    // have a text-string suffix. If there is a suffix, we'll use that
    // as the name. Otherwise, we'll return a generic name with the
    // episode ID.
    $pattern = static::getUrlRegexPattern();
    preg_match_all($pattern, $url, $matches);
    if (!empty($matches[1][0]) && !empty($matches[2][0])) {
      if (!empty($matches[3][0])) {
        $slug = $matches[3][0];
        return ucfirst(trim(str_replace("-", " ", $slug)));
      }
      else {
        $podcast_id = $matches[1][0];
        $episode_id = $matches[2][0];
        return t('Buzzsprout episode @episode_id (Podcast ID: @podcast_id)', [
          '@episode_id' => $episode_id,
          '@podcast_id' => $podcast_id,
        ]);
      }
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
      $podcast_id = $matches[1][0];
      $episode_id = $matches[2][0];
      $elements[$delta] = [
        '#theme' => 'media_remote_buzzsprout',
        '#podcast_id' => $podcast_id,
        '#episode_id' => $episode_id,
      ];
    }
    return $elements;
  }

}
