<?php

namespace Drupal\media_remote;

/**
 * Defines an interface for a formatter that deals with Remote Media assets.
 */
interface MediaRemoteFormatterInterface {

  /**
   * Gets the REGEX pattern that should be used to validate this URL value.
   *
   * @return string
   *   The REGEX pattern, using "/" as enclosing chars.
   */
  public static function getUrlRegexPattern();

  /**
   * Provides examples of valid URLs for this formatter.
   *
   * @return string[]
   *   An indexed list where each value is a valid example of URLs handled
   *   by this formatter.
   */
  public static function getValidUrlExampleStrings(): array;

  /**
   * Tries to identify a default name for a given URL.
   *
   * @param string $url
   *   The source URL being used for this media entity.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The translated string to be used in the source plugin as default name.
   */
  public static function deriveMediaDefaultNameFromUrl($url);

}
