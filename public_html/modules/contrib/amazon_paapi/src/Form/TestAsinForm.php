<?php

namespace Drupal\amazon_paapi\Form;

use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResource;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType;
use Drupal\amazon_paapi\AmazonPaapi;
use Drupal\amazon_paapi\AmazonPaapiTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;

/**
 * Debug Amazon PA API response for an ASIN.
 */
class TestAsinForm extends FormBase {

  use AmazonPaapiTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'amazon_paapi_test_asin';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [
      '#title' => $this->t('Amazon PA API 5.0 ASIN response'),
    ];

    $form['description'] = [
      '#markup' => $this->t('Shows the API response for a GetItem request to Amazon.'),
    ];

    if (!empty($_SESSION['amazon_paapi.debug_asin.response'])) {
      $form['debug_output'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Response'),
        '#default_value' => var_export($_SESSION['amazon_paapi.debug_asin.response'], TRUE),
        '#rows' => 20,
        '#attributes' => [
          'disabled' => 'disabled',
        ],
      ];

      unset($_SESSION['amazon_paapi.debug_asin.response']);
    }

    $form['asin'] = [
      '#type' => 'textfield',
      '#title' => 'ASIN',
      '#size' => 20,
      '#maxlength' => 20,
      '#description' => $this->t("Amazon Standard Identification Numbers (ASINs) are unique blocks of 10 letters and/or numbers that identify items.<BR>You can find the ASIN on the item's product information page at Amazon."),
    ];

    $form['execute']['actions'] = ['#type' => 'actions'];
    $form['execute']['actions']['op'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send request'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $asin = $form_state->getValue('asin');
    $result = $this->fetchProductData($asin);

    if (!empty($result['response'])) {
      $_SESSION['amazon_paapi.debug_asin.response'] = $result['response'];
    }
    if (!empty($result['info'])) {
      foreach ($result['info'] as $info) {
        $this->messenger()->addStatus($info);
      }
    }
    if (!empty($result['errors'])) {
      foreach ($result['errors'] as $error) {
        $this->messenger()->addWarning($error);
      }
    }
  }

  /**
   * Fetches product data from amazon.
   *
   * @param string $asin
   *   Amazon ASIN number.
   *
   * @return array
   *   Array with keys: errors => [] and info => [].
   */
  protected function fetchProductData($asin) {
    $result = [
      'errors' => [],
      'info' => [],
      'response' => NULL,
    ];

    $resources = [
      GetItemsResource::BROWSE_NODE_INFOBROWSE_NODES,
      GetItemsResource::IMAGESPRIMARYSMALL,
      GetItemsResource::IMAGESPRIMARYMEDIUM,
      GetItemsResource::IMAGESPRIMARYLARGE,
      GetItemsResource::IMAGESVARIANTSSMALL,
      GetItemsResource::IMAGESVARIANTSMEDIUM,
      GetItemsResource::IMAGESVARIANTSLARGE,
      GetItemsResource::ITEM_INFOTITLE,
      GetItemsResource::ITEM_INFOBY_LINE_INFO,
      GetItemsResource::ITEM_INFOCLASSIFICATIONS,
      GetItemsResource::OFFERSLISTINGSAVAILABILITYMESSAGE,
      GetItemsResource::OFFERSLISTINGSPRICE,
      GetItemsResource::OFFERSLISTINGSPROMOTIONS,
      GetItemsResource::OFFERSLISTINGSSAVING_BASIS,
      GetItemsResource::OFFERSLISTINGSDELIVERY_INFOIS_PRIME_ELIGIBLE,
      GetItemsResource::CUSTOMER_REVIEWSSTAR_RATING,
      GetItemsResource::CUSTOMER_REVIEWSCOUNT,
    ];

    $request = new GetItemsRequest();
    $request->setItemIds([$asin]);
    $request->setPartnerTag(AmazonPaapi::getPartnerTag());
    $request->setPartnerType(PartnerType::ASSOCIATES);
    $request->setResources($resources);

    try {
      $response = $this->getAmazonPaapi()->getApi()->getItems($request);
      if ($response->getItemsResult() && $response->getItemsResult()->getItems()) {
        $item = $response->getItemsResult()->getItems()[0];
        $result['response'] = $item;

        if ($item->getASIN()) {
          $result['info'][] = 'ASIN: ' . $item->getASIN();
        }

        if (
          $item->getItemInfo()
          && $item->getItemInfo()->getTitle()
          && $item->getItemInfo()->getTitle()->getDisplayValue()
        ) {
          $result['info'][] = 'Title: ' . $item->getItemInfo()->getTitle()->getDisplayValue();
        }

        if ($item->getDetailPageURL()) {
          $result['info'][] = 'Detail Page URL: ' . $item->getDetailPageURL();
        }

        if (
          $item->getOffers()
          && $item->getOffers()->getListings()
          && $item->getOffers()->getListings()[0]->getPrice()
          && $item->getOffers()->getListings()[0]->getPrice()->getDisplayAmount()
        ) {
          $result['info'][] = 'Buying price: ' . $item->getOffers()->getListings()[0]->getPrice()
            ->getDisplayAmount();
        }
      }

      if ($response->getErrors()) {
        $result['errors'][] = "Printing first error object from list of errors:";
        $result['errors'][] = $response->getErrors()[0]->getCode();
        $result['errors'][] = $response->getErrors()[0]->getMessage();
      }
    }
    catch (\Exception $e) {
      $errors = $this->getAmazonPaapi()->logException($e, FALSE);
      $result['errors'] = array_merge($result['errors'], $errors);
    }

    return $result;
  }

}
