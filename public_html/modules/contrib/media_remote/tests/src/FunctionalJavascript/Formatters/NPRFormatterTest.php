<?php

namespace Drupal\Tests\media_remote\FunctionalJavascript\Formatters;

use Drupal\Tests\media_remote\FunctionalJavascript\MediaRemoteFunctionalJavascriptTestBase;

/**
 * Tests the NPR formatter.
 *
 * @group media_remote
 */
class NPRFormatterTest extends MediaRemoteFunctionalJavascriptTestBase {

  /**
   * Tests the NPR formatter.
   */
  public function testNPR() {
    $assert_session = $this->assertSession();
    $session = $this->getSession();
    $page = $session->getPage();

    // Set the formatter on the media source field.
    $source_field_name = $this->mediaType->getSource()->getSourceFieldDefinition($this->mediaType)->getName();
    \Drupal::service('entity_display.repository')
      ->getViewDisplay('media', $this->mediaType->id(), 'default')
      ->setComponent($source_field_name, [
        'type' => 'media_remote_npr',
      ])
      ->save();

    $node_title = 'Node with a NPR media';
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
    $assert_session->pageTextContains("The given URL is not valid. Valid values are in the format: https://livesessions.npr.org/embed/v2/videos/[video-id]");
    // Enter a valid URL and save the media item.
    $assert_session->elementExists('css', '.media-library-widget-modal input[name="url"]')
      ->setValue('https://livesessions.npr.org/embed/v2/videos/fdf413b3-73be-432b-97b3-43261b3a1376');
    $assert_session->elementExists('css', '.media-library-widget-modal input[value="Add"]')
      ->press();
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->pageTextNotContains("The given URL is not valid");
    $media_name = 'NPR video from https://livesessions.npr.org/embed/v2/videos/fdf413b3-73be-432b-97b3-43261b3a1376';
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
    $iframe = $assert_session->elementExists('css', 'article.node--type-node-type .field--name-field-media-media-remote iframe[src^="https://livesessions.npr.org/"]');
    // Assert the default size is applied, since we haven't configured any.
    $this->assertSame('960', (string) $iframe->getAttribute('width'));
    $this->assertSame('600', (string) $iframe->getAttribute('height'));
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
    $iframe = $assert_session->elementExists('css', 'article.node--type-node-type .field--name-field-media-media-remote iframe[src^="https://livesessions.npr.org/"]');
    $this->assertSame('1000', (string) $iframe->getAttribute('width'));
    $this->assertSame('700', (string) $iframe->getAttribute('height'));
  }

}

