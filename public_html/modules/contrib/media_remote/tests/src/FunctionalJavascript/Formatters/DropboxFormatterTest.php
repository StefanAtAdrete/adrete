<?php

namespace Drupal\Tests\media_remote\FunctionalJavascript\Formatters;

use Drupal\Tests\media_remote\FunctionalJavascript\MediaRemoteFunctionalJavascriptTestBase;

/**
 * Tests the Dropbox formatter.
 *
 * @group media_remote
 */
class DropboxFormatterTest extends MediaRemoteFunctionalJavascriptTestBase {

  /**
   * Tests the Dropbox formatter.
   */
  public function testDropbox() {
    $assert_session = $this->assertSession();
    $session = $this->getSession();
    $page = $session->getPage();

    // Set the formatter on the media source field.
    $source_field_name = $this->mediaType->getSource()->getSourceFieldDefinition($this->mediaType)->getName();
    \Drupal::service('entity_display.repository')
      ->getViewDisplay('media', $this->mediaType->id(), 'default')
      ->setComponent($source_field_name, [
        'type' => 'media_remote_dropbox',
        'settings' => [
          // Save a dummy app key just so we can close the form. The iframe
          // contents will not load without it, but for our test we'll be OK if
          // the iframe is there and sized properly.
          'app_key' => 'foobarbaz',
        ],
      ])
      ->save();

    $node_title = 'Node with a Dropbox media';
    $node = $this->drupalCreateNode([
      'type' => $this->nodeType->id(),
      'title' => $node_title,
    ]);
    $this->drupalGet($node->toUrl('edit-form'));
    $this->openMediaLibraryForField('field_node__remote_media');
    // Enter an invalid URL and check the error message.
    $assert_session->elementExists('css', '.media-library-widget-modal input[name="url"]')
      ->setValue('https://drupal.org');
    $assert_session->elementExists('css', '.media-library-widget-modal input[value="Add"]')
      ->press();
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains("The given URL is not valid. Valid values are in the format: https://www.dropbox.com/s/u0bdwmkjmqld9l2/dbx-supporting-distributed-work.gif?dl=0 or https://www.dropbox.com/s/ttqabn104bujvcv/Primeros%20pasos%20con%20Dropbox.pdf?dl=0");
    // Enter a valid URL and save the media item.
    $assert_session->elementExists('css', '.media-library-widget-modal input[name="url"]')
      ->setValue('https://www.dropbox.com/s/ttqabn104bujvcv/Primeros%20pasos%20con%20Dropbox.pdf?dl=0');
    $assert_session->elementExists('css', '.media-library-widget-modal input[value="Add"]')
      ->press();
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->pageTextNotContains("The given URL is not valid");
    $media_name = 'Dropbox document from https://www.dropbox.com/s/ttqabn104bujvcv/Primeros%20pasos%20con%20Dropbox.pdf?dl=0';
    $name_input = $assert_session->waitForElement('css', '.media-library-widget-modal input[name="media[0][fields][name][0][value]"]');
    $this->assertNotNull($name_input);
    $this->assertSame($media_name, $name_input->getValue());
    // Save the modal and the node form.
    $this->pressSaveButton();
    $this->pressInsertSelected();
    $name_in_widget = $assert_session->elementExists('css', '.field--name-field-node__remote-media .media-library-item__name');
    $this->assertSame($media_name, $name_in_widget->getText());
    $page->pressButton('Save');
    $assert_session->pageTextContains("{$this->nodeType->label()} {$node_title} has been updated");
    $session->wait(1000);
    // Iframe is there.
    $assert_session->elementExists('css', 'article.node--type-node-type .field--name-field-media-media-remote iframe[src^="https://www.dropbox.com/"]');
    // Assert the default size is applied, since we haven't configured any.
    $container = $assert_session->elementExists('css', 'article.node--type-node-type .field--name-field-media-media-remote .dropbox-embed-container');
    $this->assertStringContainsString('height: 600', $container->getAttribute('style'));
    $this->assertStringContainsString('width: 960', $container->getAttribute('style'));
    // Change the formatter settings and verify the iframe resizes.
    $this->drupalGet("/admin/structure/media/manage/{$this->mediaType->id()}/display");
    $assert_session->elementExists('css', 'input[data-drupal-selector="edit-fields-field-media-media-remote-settings-edit"]')
      ->click();
    $width_element = $assert_session->waitForElement('css', 'input[data-drupal-selector="edit-fields-field-media-media-remote-settings-edit-form-settings-width"]');
    $width_element->setValue(1000);
    $height_element = $assert_session->elementExists('css', 'input[data-drupal-selector="edit-fields-field-media-media-remote-settings-edit-form-settings-height"]');
    $height_element->setValue(700);
    $assert_session->elementExists('css', 'input[data-drupal-selector="edit-fields-field-media-media-remote-settings-edit-form-actions-save-settings"]')
      ->click();
    $assert_session->assertWaitOnAjaxRequest();
    $page->pressButton('Save');
    $assert_session->pageTextContains('Your settings have been saved');
    $this->drupalGet($node->toUrl());
    $container = $assert_session->elementExists('css', 'article.node--type-node-type .field--name-field-media-media-remote .dropbox-embed-container');
    $this->assertStringContainsString('height: 700', $container->getAttribute('style'));
    $this->assertStringContainsString('width: 1000', $container->getAttribute('style'));
  }

}

