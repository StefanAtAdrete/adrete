<?php

namespace Drupal\media_remote\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'media_remote_documentcloud' formatter.
 *
 * @FieldFormatter(
 *   id = "media_remote_documentcloud",
 *   label = @Translation("Remote Media - DocumentCloud"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class MediaRemoteDocumentcloudFormatter extends MediaRemoteFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function getUrlRegexPattern() {
    return '/^https:\/\/www\.documentcloud\.org\/documents\/([\d]+)(-*.*)/';
  }

  /**
   * {@inheritdoc}
   */
  public static function getValidUrlExampleStrings(): array {
    return [
      'https://www.documentcloud.org/documents/21034688',
      'https://www.documentcloud.org/documents/21034688-response-to-representative-allen-8-3-2021',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function deriveMediaDefaultNameFromUrl($url) {
    $matches = [];
    // DocumentCloud URLs can either contain only Document ID, or
    // have a text-string suffix. If there is a suffix, we'll use that
    // as the name. Otherwise, we'll return a generic name with the
    // document ID.
    $pattern = static::getUrlRegexPattern();
    preg_match_all($pattern, $url, $matches);
    if (!empty($matches[1][0])) {
      if (!empty($matches[2][0])) {
        $slug = $matches[2][0];
        return ucfirst(trim(str_replace("-", " ", $slug)));
      }
      else {
        $document_id = $matches[1][0];
        return t('DocumentCloud document @document_id', [
          '@document_id' => $document_id,
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
      if (empty($matches[1][0])) {
        continue;
      }
      $document_id = $matches[1][0];
      $elements[$delta] = [
        '#theme' => 'media_remote_documentcloud',
        '#document_id' => $document_id,
        '#slug' => $matches[2][0] ?? '',
      ];
    }
    return $elements;
  }

}
