<?php

namespace Drupal\media_remote\Plugin\media\Source;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\media\MediaInterface;
use Drupal\media\MediaSourceBase;
use Drupal\media\MediaSourceFieldConstraintsInterface;

/**
 * A media source plugin for non-OEmbed remote media assets.
 *
 * @MediaSource(
 *   id = "media_remote",
 *   label = @Translation("Remote Media URL"),
 *   description = @Translation("A non-OEmbed media source plugin for remote content."),
 *   allowed_field_types = {"string"},
 *   default_thumbnail_filename = "generic.png",
 *   forms = {
 *     "media_library_add" = "Drupal\media_remote\Form\MediaRemoteMediaForm"
 *   }
 * )
 */
class MediaRemoteSource extends MediaSourceBase implements MediaSourceFieldConstraintsInterface {

  /**
   * Key for "Name" metadata attribute.
   *
   * @var string
   */
  const METADATA_ATTRIBUTE_NAME = 'name';

  /**
   * {@inheritdoc}
   */
  public function getMetadataAttributes() {
    return [
      static::METADATA_ATTRIBUTE_NAME => $this->t('Name'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(MediaInterface $media, $attribute_name) {
    $url = $media->get($this->configuration['source_field'])->value;
    // If the source field is not required, it may be empty.
    if (empty($url)) {
      return parent::getMetadata($media, $attribute_name);
    }
    switch ($attribute_name) {
      case static::METADATA_ATTRIBUTE_NAME:
      case 'default_name':
        // Formatters know how to convert the URL into a default name string.
        $formatter_class = $this->getFormatterClass($media);
        return $formatter_class::deriveMediaDefaultNameFromUrl($url);

      default:
        return parent::getMetadata($media, $attribute_name);
    }
  }

  /**
   * Figure out the formatter class to be used on a given media entity.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media entity we are interested in.
   *
   * @return string
   *   The FQN of the formatter class configured in the `default` media display
   *   for the media source field.
   */
  public function getFormatterClass(MediaInterface $media) {
    $field_definition = $this->getSourceFieldDefinition($media->bundle->entity);

    // @todo There is probably a better way for this class to figure out what
    // formatter class is being used.
    /** @var EntityViewDisplayInterface $display */
    $display = EntityViewDisplay::load('media.' . $media->bundle() . '.default');
    $components = $display->getComponents();
    $formatter_config = $components[$field_definition->getName()] ?? [];
    if (empty($formatter_config['settings']['formatter_class'])) {
      throw new \LogicException('The Remote Media validator needs the _default_ media display to be configured, and for the source field to use any of the formatters provided by the Media Remote module.');
    }

    // See MediaRemoteFormatterBase::defaultSettings() for where this is
    // defined/enforced.
    return $formatter_config['settings']['formatter_class'];
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceFieldConstraints() {
    return [
      'media_remote' => [],
    ];
  }

}
