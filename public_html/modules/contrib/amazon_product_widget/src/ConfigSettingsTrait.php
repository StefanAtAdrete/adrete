<?php

namespace Drupal\amazon_product_widget;

/**
 * Facilitates configuring classes settings.
 */
trait ConfigSettingsTrait {

  /**
   * Gets all available settings keys.
   *
   * @return array
   *   Class' SETTINGS_ constants keys (and values).
   *
   * @throws \ReflectionException
   */
  public static function getAvailableSettingsKeys() {
    $settings_keys = [];
    $reflect = new \ReflectionClass(static::class);
    foreach ($reflect->getConstants() as $key => $value) {
      if (strpos($key, 'SETTINGS_') === 0) {
        $settings_keys[$key] = $value;
      }
    }

    return $settings_keys;
  }

}
