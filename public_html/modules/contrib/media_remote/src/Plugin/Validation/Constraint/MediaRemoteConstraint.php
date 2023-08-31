<?php

namespace Drupal\media_remote\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if a value represents a valid remote resource URL.
 *
 * @Constraint(
 *   id = "media_remote",
 *   label = @Translation("Media Remote resource", context = "Validation"),
 *   type = {"string"}
 * )
 */
class MediaRemoteConstraint extends Constraint {

  /**
   * The error message if the URL is empty.
   *
   * @var string
   */
  public $emptyUrlMessage = 'The URL cannot be empty.';

  /**
   * The error message if the URL does not match.
   *
   * @var string
   */
  public $invalidUrlMessage = 'The given URL is not valid. Valid values are in the format: @example_urls';

}
