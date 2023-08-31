<?php

namespace Drupal\amazon_paapi;

use Amazon\ProductAdvertisingAPI\v1\ApiException;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\ProductAdvertisingAPIClientException;
use Amazon\ProductAdvertisingAPI\v1\Configuration;
use Drupal\Core\Logger\LoggerChannelTrait;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * Service wrapper for Amazon PA API SDK.
 */
class AmazonPaapi {

  use LoggerChannelTrait;

  /**
   * The settings keys in `amazon_paapi.settings`.
   *
   * These can be overridden by environment variables.
   *
   * @see getSetting()
   */
  const SETTINGS_ACCESS_KEY    = 'access_key';
  const SETTINGS_ACCESS_SECRET = 'access_secret';
  const SETTINGS_HOST          = 'host';
  const SETTINGS_REGION        = 'region';
  const SETTINGS_PARTNER_TAG   = 'partner_tag';

  /**
   * The server environment variables for specifying api credentials.
   *
   * Setting an environment variable with that name will override the
   * corresponding setting in `amazon_paapi.settings`.
   *
   * @see getSetting()
   */
  const ENV_ACCESS_KEY    = 'AMAZON_PAAPI_ACCESS_KEY';
  const ENV_ACCESS_SECRET = 'AMAZON_PAAPI_ACCESS_SECRET';
  const ENV_HOST          = 'AMAZON_PAAPI_HOST';
  const ENV_REGION        = 'AMAZON_PAAPI_REGION';
  const ENV_PARTNER_TAG   = 'AMAZON_PAAPI_PARTNER_TAG';

  /**
   * Gets the Amazon PA API.
   *
   * @param \GuzzleHttp\ClientInterface|null $client
   *   (optional) Provide a custom http client.
   *
   * @return \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi
   */
  public function getApi(ClientInterface $client = NULL) {
    $config = new Configuration();
    $config->setAccessKey(static::getAccessKey());
    $config->setSecretKey(static::getAccessSecret());
    $config->setHost(static::getHost());
    $config->setRegion(static::getRegion());

    if (empty($client)) {
      $client = new Client();
    }
    $api = new DefaultApi($client, $config);
    return $api;
  }

  /**
   * Gets the logger.
   *
   * @return \Psr\Log\LoggerInterface
   */
  public function logger() {
    return $this->getLogger('amazon_paapi');
  }

  /**
   * Parse & log exception.
   *
   * @param \Exception $e
   *   Exception catched when using the api.
   * @param bool $log
   *   Write to module log (amazon_paapi).
   *
   * @return string[]
   */
  public function logException(\Exception $e, $log = TRUE) {
    $messages = [];
    if ($e instanceof ApiException) {
      $messages[] = "HTTP Status Code: " . $e->getCode();
      $messages[] = "Error Message: " . $e->getMessage();
      if ($e->getResponseObject() instanceof ProductAdvertisingAPIClientException) {
        $errors = $e->getResponseObject()->getErrors();
        foreach ($errors as $error) {
          $messages[] = "Error Type: " . $error->getCode();
          $messages[] = "Error Message: " . $error->getMessage();
        }
      }
      else {
        $messages[] = "Error response body: " . $e->getResponseBody();
      }
    }
    else {
      $messages[] = $e->getMessage();
    }

    if ($log) {
      $this->logger()->error(implode("<BR>", $messages));
    }
    return $messages;
  }

  /**
   * Returns the secret key needed for the api.
   *
   * @return string|bool
   */
  public static function getAccessSecret() {
    return static::getSetting(static::SETTINGS_ACCESS_SECRET);
  }

  /**
   * Returns the access key for the api.
   *
   * @return string|bool
   */
  public static function getAccessKey() {
    return static::getSetting(static::SETTINGS_ACCESS_KEY);
  }

  /**
   * Gets the region for the api.
   *
   * @see https://webservices.amazon.com/paapi5/documentation/common-request-parameters.html#host-and-region
   *
   * @return string|bool
   */
  public static function getRegion() {
    return static::getSetting(static::SETTINGS_REGION);
  }

  /**
   * Returns the host for the api.
   *
   * @see https://webservices.amazon.com/paapi5/documentation/common-request-parameters.html#host-and-region
   *
   * @return string|bool
   */
  public static function getHost() {
    return static::getSetting(static::SETTINGS_HOST);
  }

  /**
   * Returns the partner tag / associates id for the api.
   *
   * @return string|bool
   */
  public static function getPartnerTag() {
    return static::getSetting(static::SETTINGS_PARTNER_TAG);
  }

  /**
   * Gets the environment variable name for a settings key.
   *
   * @param string $settings_key
   *   Any of self::SETTINGS_*.
   *
   * @return bool|string
   *   Any of self::ENV_* or FALSE if not found.
   */
  public static function getEnvVariable($settings_key) {
    $env_map = [
      self::SETTINGS_ACCESS_KEY    => self::ENV_ACCESS_KEY,
      self::SETTINGS_ACCESS_SECRET => self::ENV_ACCESS_SECRET,
      self::SETTINGS_HOST          => self::ENV_HOST,
      self::SETTINGS_REGION        => self::ENV_REGION,
      self::SETTINGS_PARTNER_TAG   => self::ENV_PARTNER_TAG,
    ];

    return !empty($env_map[$settings_key]) ? $env_map[$settings_key] : FALSE;
  }

  /**
   * Gets the settings value from either the environment or config.
   *
   * @param string $settings_key
   *   Any of self::SETTINGS_*.
   *
   * @return bool|string
   *   The settings value or FALSE if not found.
   */
  public static function getSetting($settings_key) {
    if ($env_var = static::getEnvVariable($settings_key)) {
      if ($value = getenv($env_var)) {
        return $value;
      }
    }
    if ($value = \Drupal::config('amazon_paapi.settings')->get($settings_key)) {
      return $value;
    }

    return FALSE;
  }

  /**
   * Gets all available settings keys.
   *
   * @return array
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

  /**
   * Checks whether the specific setting is set via environment variable.
   *
   * @param string $settings_key
   *   Any of self::SETTINGS_*.
   *
   * @return bool
   */
  public static function isSetInEnv($settings_key) {
    $env_var = static::getEnvVariable($settings_key);
    $value = getenv($env_var);
    return !empty($value);
  }

}
