# Amazon Product Advertising API

Provides a service to load the Amazon PA API SDK 5.0 with credentials that
are saved in Drupal. This is supposed to be a lean module, requests must be
assembled using the classes in the SDK as there are many possibilities to 
compose a request. Which also means that error handling is up to the developer
who implements them.

## Installation

Install the module via composer:

    composer require drupal/amazon_paapi
    
Enable the module as you would a normal drupal module.

Sign up for an Amazon AWS account to use the Product Advertising Service.

Enter the api credentials, partner tag and region details on
    
    /admin/config/services/amazon-paapi/settings 

The credentials can also be set in environment variables, if done so they will 
override settings, the environment variables are:

    AMAZON_PAAPI_ACCESS_KEY
    AMAZON_PAAPI_ACCESS_SECRET
    AMAZON_PAAPI_HOST
    AMAZON_PAAPI_REGION
    AMAZON_PAAPI_PARTNER_TAG

## Usage

For an example how to create a request see the test ASIN page

    /admin/config/services/amazon-paapi/test-asin

For further examples see [drunomics/paapi5-php-sdk](https://github.com/drunomics/php-paapi5-sdk) 
which will be installed via composer when installing the module.
