<?php

namespace Drupal\amazon_paapi\Form;

use Drupal\amazon_paapi\AmazonPaapi;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Amazon PA API for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'amazon_paapi.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'amazon_paapi_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $access_key = AmazonPaapi::getAccessKey();
    $access_secret = AmazonPaapi::getAccessSecret();
    $host = AmazonPaapi::getHost();
    $region = AmazonPaapi::getRegion();
    $partner_tag = AmazonPaapi::getPartnerTag();

    $form['description'] = [
      '#markup' => $this->t('You must register as an <a href=":url">Associate with Amazon</a> before using this module.', [':url' => 'https://affiliate-program.amazon.com/']),
    ];

    $description = $this->t('Enter your Access Key ID here.');
    if (empty($access_key)) {
      $description = $this->t('You must sign up for an Amazon AWS account to use the Product Advertising Service. See the <a href=":url">AWS home page</a> for information and a registration form.', [':url' => 'https://aws-portal.amazon.com/gp/aws/developer/account/index.html?ie=UTF8&action=access-key']);
    }
    $form[AmazonPaapi::SETTINGS_ACCESS_KEY] = [
      '#type' => 'textfield',
      '#title' => $this->t('AWS Access Key ID'),
      '#required' => TRUE,
      '#default_value' => $access_key,
      '#description' => $description,
    ];

    $description = $this->t('Enter your Access Key Secret here.');
    if (empty($access_secret)) {
      $description = $this->t('You must sign up for an Amazon AWS account to use the Product Advertising Service. See the <a href=":url">AWS home page</a> for information and a registration form.', [':url' => 'https://aws-portal.amazon.com/gp/aws/developer/account/index.html?ie=UTF8&action=access-key']);
    }
    $form[AmazonPaapi::SETTINGS_ACCESS_SECRET] = [
      '#type' => 'textfield',
      '#title' => $this->t('AWS Access Secret'),
      '#required' => TRUE,
      '#default_value' => $access_secret,
      '#description' => $description,
    ];

    $host_url = 'https://webservices.amazon.com/paapi5/documentation/common-request-parameters.html#host-and-region';

    $form[AmazonPaapi::SETTINGS_HOST] = [
      '#type' => 'textfield',
      '#title' => $this->t('Host'),
      '#required' => TRUE,
      '#default_value' => $host,
      '#description' => $this->t('The AWS region of the target locale to which you are sending requests. For more information and valid values refer <a href=":url">Host</a>.', [':url' => $host_url]),
    ];

    $region_url = 'https://webservices.amazon.com/paapi5/documentation/common-request-parameters.html#host-and-region';

    $form[AmazonPaapi::SETTINGS_REGION] = [
      '#type' => 'textfield',
      '#title' => $this->t('Region'),
      '#required' => TRUE,
      '#default_value' => $region,
      '#description' => $this->t('The AWS region of the target locale to which you are sending requests. For more information and valid values refer <a href=":url">Region</a>.', [':url' => $region_url]),
    ];

    $form[AmazonPaapi::SETTINGS_PARTNER_TAG] = [
      '#type' => 'textfield',
      '#title' => $this->t('Partner Tag / Associates ID'),
      '#required' => TRUE,
      '#default_value' => $partner_tag,
    ];

    foreach (AmazonPaapi::getAvailableSettingsKeys() as $settings_key) {
      if (AmazonPaapi::isSetInEnv($settings_key)) {
        $form[$settings_key]['#disabled'] = TRUE;
        $form[$settings_key]['#required'] = FALSE;
        $description = $this->t("This setting is set via environment variable %env and can't be changed unless it is unset.", ['%env' => AmazonPaapi::getEnvVariable($settings_key)]);
        if (!empty($form[$settings_key]['#description'])) {
          $description .= "<BR>" . $form[$settings_key]['#description'];
        }
        $form[$settings_key]['#description'] = $description;
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('amazon_paapi.settings');

    foreach (AmazonPaapi::getAvailableSettingsKeys() as $settings_key) {
      if (!AmazonPaapi::isSetInEnv($settings_key)) {
        $config->set($settings_key, $form_state->getValue($settings_key));
      }
    }

    $config->save();
  }

}
