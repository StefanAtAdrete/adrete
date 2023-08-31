<?php

namespace Drupal\media_remote\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\media_remote\Plugin\media\Source\MediaRemoteSource;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates Media Remote URLs.
 */
class MediaRemoteConstraintValidator extends ConstraintValidator {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    /** @var \Drupal\media\MediaInterface $media */
    $media = $value->getEntity();
    $source = $media->getSource();
    if (!($source instanceof MediaRemoteSource)) {
      throw new \LogicException('Media source must implement ' . MediaRemoteSource::class);
    }

    $url = $source->getSourceFieldValue($media);
    $url = trim($url, "/");
    // The URL may be NULL if the source field is empty, which is invalid input.
    if (empty($url)) {
      $this->context->addViolation($constraint->emptyUrlMessage);
      return;
    }

    $formatter_class = $source->getFormatterClass($media);
    $pattern = $formatter_class::getUrlRegexPattern();
    $example_urls = $formatter_class::getValidUrlExampleStrings();
    if (!preg_match($pattern, $url)) {
      $this->context->addViolation($constraint->invalidUrlMessage, [
        '@example_urls' => media_remote_oxford_join($example_urls, $this->t('or')),
      ]);
    }
  }

}
