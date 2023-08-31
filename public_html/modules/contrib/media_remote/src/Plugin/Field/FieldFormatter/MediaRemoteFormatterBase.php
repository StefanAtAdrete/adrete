<?php

namespace Drupal\media_remote\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\media\Entity\MediaType;
use Drupal\media_remote\MediaRemoteFormatterInterface;
use Drupal\media_remote\Plugin\media\Source\MediaRemoteSource;

/**
 * Base class for Media Remote formatters.
 */
abstract class MediaRemoteFormatterBase extends FormatterBase implements MediaRemoteFormatterInterface {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'formatter_class' => static::class,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // Only allow choosing this formatter if the media type is configured to
    // use the "Media Remote" source plugin.
    $entity_type_id = $field_definition->getTargetEntityTypeId();
    if ($entity_type_id === 'media') {
      $bundle = $field_definition->getTargetBundle();
      if (!empty($bundle)) {
        $media_type = MediaType::load($bundle);
        if (!empty($media_type)) {
          $source = $media_type->getSource();
          if ($source && ($source instanceof MediaRemoteSource)) {
            return TRUE;
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function deriveMediaDefaultNameFromUrl($url) {
    return t('Remote media for @url', ['@url' => $url]);
  }

}
