<?php

namespace Drupal\media_remote\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'media_remote_quickbase' formatter.
 *
 * @FieldFormatter(
 *   id = "media_remote_quickbase",
 *   label = @Translation("Remote Media - Quickbase"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class MediaRemoteQuickbaseFormatter extends MediaRemoteFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function getUrlRegexPattern() {
    return '/^https:\/\/([A-Za-z]+)\.quickbase\.com\/db\/(.*)/';
  }

  /**
   * {@inheritdoc}
   */
  public static function getValidUrlExampleStrings(): array {
    return [
      'https://acme.quickbase.com/db/bc8fj3u7k?a=API_GenResultsTable&qid=11&jht=1',
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
      $elements[$delta] = [
        '#theme' => 'media_remote_quickbase',
        '#url' => $item->value,
      ];
    }
    return $elements;
  }

}
